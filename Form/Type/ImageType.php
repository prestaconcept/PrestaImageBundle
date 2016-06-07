<?php

namespace Presta\ImageBundle\Form\Type;

use Presta\ImageBundle\Form\DataTransformer\Base64ToImageTransformer;
use Presta\ImageBundle\Model\AspectRatio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
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
     * @param TranslatorInterface $translator
     * @param StorageInterface    $storage
     */
    public function __construct(TranslatorInterface $translator, StorageInterface $storage)
    {
        $this->translator = $translator;
        $this->storage = $storage;
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
            ->setDefault('aspect_ratios', $aspectRatios)
            ->setDefault('max_width', 320)
            ->setDefault('max_height', 180)
            ->setDefault('download_uri', null)
            ->setDefault('download_link', true)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['aspect_ratios'] = $options['aspect_ratios'];
        $view->vars['max_width'] = $options['max_width'];
        $view->vars['max_height'] = $options['max_height'];
        $view->vars['object'] = $form->getParent()->getData();

        if ($options['download_link'] && $view->vars['object']) {
            $view->vars['download_uri'] = $this->storage->resolveUri($form->getParent()->getData(), $form->getName());
        }
    }

    /**
     * @param array $aspectRatios
     * @param float $value
     * @param string $key
     * @param bool $checked
     */
    private function addAspectRatio(array &$aspectRatios, $value, $key, $checked = false)
    {
        $label = $this->translator->trans(sprintf('aspect_ratio.%s', $value), [], 'PrestaImageBundle');

        $aspectRatios[$key] = new AspectRatio($value, $label, $checked);
    }
}
