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
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;

class ImageType extends AbstractType
{
    private $storage;
    private $handler;

    public function __construct(StorageInterface $storage, UploadHandler $handler)
    {
        $this->storage = $storage;
        $this->handler = $handler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('base64', HiddenType::class, [
                'required' => $options['required'],
                'attr' => [
                    'class' => 'cropper-base64',
                ],
            ])
        ;

        $builder->addModelTransformer(new Base64ToImageTransformer());

        if ($options['allow_delete'] && !$options['required']) {
            $this->buildDeleteField($builder, $options);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $aspectRatios = [];

        $this->setAspectRatio($aspectRatios, '16:9', 1.78);
        $this->setAspectRatio($aspectRatios, '4:3', 1.33);
        $this->setAspectRatio($aspectRatios, '1', 1);
        $this->setAspectRatio($aspectRatios, '2:3', 0.66);
        $this->setAspectRatio($aspectRatios, 'nan', null, true);

        $resolver
            ->setDefault('allow_delete', true)
            ->setDefault('delete_label', 'btn_delete')
            ->setDefault('aspect_ratios', $aspectRatios)
            ->setDefault('cropper_options', ['autoCropArea' => 1])
            ->setDefault('max_width', 320)
            ->setDefault('max_height', 180)
            ->setDefault('preview_width', function (Options $options) {
                return sprintf('%dpx', $options['max_width']);
            })
            ->setDefault('preview_height', function (Options $options) {
                return sprintf('%dpx', $options['max_height']);
            })
            ->setDefault('upload_button_class', '')
            ->setDefault('cancel_button_class', '')
            ->setDefault('save_button_class', '')
            ->setDefault('download_uri', null)
            ->setDefault('download_link', true)
            ->setDefault('file_upload_enabled', true)
            ->setDefault('remote_url_enabled', true)
            ->setDefault('rotation_enabled', false)
            ->setDefault('translation_domain', 'PrestaImageBundle')
            ->setDefault('upload_mimetype', 'image/png')
            ->setDefault('upload_quality', 0.92)  // default value: https://developer.mozilla.org/de/docs/Web/API/HTMLCanvasElement/toDataURL
            ->setDefault('error_bubbling', false)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['aspect_ratios'] = $options['aspect_ratios'];
        $view->vars['cropper_options'] = json_encode($options['cropper_options']);
        $view->vars['max_width'] = $options['max_width'];
        $view->vars['max_height'] = $options['max_height'];
        $view->vars['preview_width'] = $options['preview_width'];
        $view->vars['preview_height'] = $options['preview_height'];
        $view->vars['upload_button_class'] = $options['upload_button_class'];
        $view->vars['cancel_button_class'] = $options['cancel_button_class'];
        $view->vars['save_button_class'] = $options['save_button_class'];
        $view->vars['file_upload_enabled'] = $options['file_upload_enabled'];
        $view->vars['remote_url_enabled'] = $options['remote_url_enabled'];
        $view->vars['rotation_enabled'] = $options['rotation_enabled'];
        $view->vars['upload_mimetype'] = $options['upload_mimetype'];
        $view->vars['upload_quality'] = $options['upload_quality'];

        if ($options['download_link'] && $form->getParent()->getData()) {
            $view->vars['download_uri'] = $this->storage->resolveUri($form->getParent()->getData(), $form->getName());
        }
    }

    private function buildDeleteField(FormBuilderInterface $builder, array $options): void
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

    private function setAspectRatio(array &$aspectRatios, string $key, ?float $value, bool $checked = false): void
    {
        $aspectRatios[$key] = new AspectRatio($value, "aspect_ratio.$key", $checked);
    }
}
