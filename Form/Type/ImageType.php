<?php

namespace Presta\ImageBundle\Form\Type;

use Presta\ImageBundle\Model\Cropper\AspectRatio;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

/**
 * @author Benoit Jouhaud <bjouhaud@prestaconcept.net>
 */
class ImageType extends VichFileType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('base64', HiddenType::class, [
            'attr' => [
                'class' => 'cropper-base64',
            ],
        ]);

        if ($options['allow_delete']) {
            $this->buildDeleteField($builder, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('aspect_ratios', [
                '16_9' => new AspectRatio(1.78, $this->translator->trans('aspect_ratio.16_9')),
                '4_3' => new AspectRatio(1.33, $this->translator->trans('aspect_ratio.4_3')),
                '1' => new AspectRatio(1, $this->translator->trans('aspect_ratio.1')),
                '2_3' => new AspectRatio(0.66, $this->translator->trans('aspect_ratio.2_3')),
                'nan' => new AspectRatio(null, $this->translator->trans('aspect_ratio.free'), true),
            ])
            ->setDefault('max_width', 320)
            ->setDefault('max_height', 180)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['aspect_ratios'] = $options['aspect_ratios'];
        $view->vars['max_width'] = $options['max_width'];
        $view->vars['max_height'] = $options['max_height'];
    }
}
