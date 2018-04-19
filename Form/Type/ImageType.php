<?php

namespace Presta\ImageBundle\Form\Type;

use Presta\ImageBundle\Form\DataTransformer\Base64ToImageTransformer;
use Presta\ImageBundle\Model\AspectRatio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Translation\TranslatorInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Benoit Jouhaud <bjouhaud@prestaconcept.net>
 */
class ImageType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var UploadHandler
     */
    protected $handler;

    /**
     * @param TranslatorInterface $translator
     * @param StorageInterface $storage
     * @param UploadHandler $handler
     */
    public function __construct(TranslatorInterface $translator, StorageInterface $storage, UploadHandler $handler)
    {
        $this->translator = $translator;
        $this->storage = $storage;
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('base64', HiddenType::class, [
                'attr' => [
                    'class' => 'cropper-base64',
                ],
            ])
        ;

        $builder->addModelTransformer(new Base64ToImageTransformer);

        if ($options['allow_delete'] && ! $options['required']) {
            $this->buildDeleteField($builder, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $aspectRatios = [];

        $this->addAspectRatio($aspectRatios, '16_9', 1.78);
        $this->addAspectRatio($aspectRatios, '4_3', 1.33);
        $this->addAspectRatio($aspectRatios, '1', 1);
        $this->addAspectRatio($aspectRatios, '2_3', 0.66);
        $this->addAspectRatio($aspectRatios, 'nan', null, true);

        $resolver
            ->setDefault('allow_delete', true)
            ->setDefault('delete_label', 'btn_delete')
            ->setDefault('aspect_ratios', $aspectRatios)
            ->setDefault('cropper_options', ['autoCropArea' => 1])
            ->setDefault('max_width', 320)
            ->setDefault('max_height', 180)
            ->setDefault('preview_width', function (Options $options) {
                return $options['max_width'];
            })
            ->setDefault('preview_height', function (Options $options) {
                return $options['max_height'];
            })
            ->setDefault('upload_button_class', 'btn btn-sm btn-info')
            ->setDefault('upload_button_icon', 'fa fa-upload')
            ->setDefault('cancel_button_class', 'btn btn-default')
            ->setDefault('save_button_class', 'btn btn-primary')
            ->setDefault('download_uri', null)
            ->setDefault('download_link', true)
            ->setDefault('enable_locale', true)
            ->setDefault('enable_remote', true)
            ->setDefault('translation_domain', 'PrestaImageBundle')
            ->setDefault('upload_mimetype', 'image/png')
            ->setDefault('upload_quality', 0.92);  // default value: https://developer.mozilla.org/de/docs/Web/API/HTMLCanvasElement/toDataURL
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['aspect_ratios'] = $options['aspect_ratios'];
        $view->vars['cropper_options'] = json_encode($options['cropper_options']);
        $view->vars['max_width'] = $options['max_width'];
        $view->vars['max_height'] = $options['max_height'];
        $view->vars['preview_width'] = $options['preview_width'];
        $view->vars['preview_height'] = $options['preview_height'];
        $view->vars['upload_button_class'] = $options['upload_button_class'];
        $view->vars['upload_button_icon'] = $options['upload_button_icon'];
        $view->vars['cancel_button_class'] = $options['cancel_button_class'];
        $view->vars['save_button_class'] = $options['save_button_class'];
        $view->vars['enable_locale'] = $options['enable_locale'];
        $view->vars['enable_remote'] = $options['enable_remote'];
        $view->vars['upload_mimetype'] = $options['upload_mimetype'];
        $view->vars['upload_quality'] = $options['upload_quality'];
        $view->vars['object'] = $form->getParent()->getData();

        if ($options['download_link'] && $view->vars['object']) {
            $view->vars['download_uri'] = $this->storage->resolveUri($form->getParent()->getData(), $form->getName());
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function buildDeleteField(FormBuilderInterface $builder, array $options)
    {
        // add delete only if there is a file
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $object = $form->getParent()->getData();

            // no object or no uploaded file: no delete button
            if (null === $object || null === $this->storage->resolvePath($object, $form->getName())) {
                return;
            }

            $form->add('delete', CheckboxType::class, [
                'label' => $options['delete_label'],
                'required' => false,
                'mapped' => false,
                'translation_domain' => $options['translation_domain']
            ]);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $delete = isset($data['delete']) ? $data['delete'] : false;
            if ($delete) {
                $data['base64'] = null;
                $event->setData($data);
            }
        });

        // delete file if needed
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $delete = $form->has('delete') ? $form->get('delete')->getData() : false;
            $entity = $form->getParent()->getData();

            if (!$delete) {
                return;
            }

            $this->handler->remove($entity, $form->getName());
        });
    }

    /**
     * @param array $aspectRatios
     * @param string $key
     * @param float $value
     * @param bool $checked
     */
    private function addAspectRatio(array &$aspectRatios, $key, $value, $checked = false)
    {
        $label = $this->translator->trans(sprintf('aspect_ratio.%s', $key), [], 'PrestaImageBundle');

        $aspectRatios[$key] = new AspectRatio($value, $label, $checked);
    }
}
}