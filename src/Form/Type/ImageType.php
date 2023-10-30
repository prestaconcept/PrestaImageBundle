<?php

namespace Presta\ImageBundle\Form\Type;

use Presta\ImageBundle\Form\DataTransformer\Base64ToImageTransformer;
use Presta\ImageBundle\Form\EventListener\ImageType\AddDeleteCheckboxListener;
use Presta\ImageBundle\Form\EventListener\ImageType\ClearBase64OnDeleteListener;
use Presta\ImageBundle\Form\EventListener\ImageType\DeleteFileListener;
use Presta\ImageBundle\Model\AspectRatio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;

class ImageType extends AbstractType
{
    private StorageInterface $storage;
    private UploadHandler $handler;

    public function __construct(StorageInterface $storage, UploadHandler $handler)
    {
        $this->storage = $storage;
        $this->handler = $handler;
    }

    /**
     * @param array{
     *     allow_delete: bool,
     *     delete_label: string,
     *     required: bool,
     *     translation_domain: string,
     * } $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'base64',
            HiddenType::class,
            [
                'required' => $options['required'],
                'attr' => [
                    'class' => 'cropper-base64',
                ],
            ],
        );

        $builder->addModelTransformer(new Base64ToImageTransformer());

        if ($options['allow_delete'] && !$options['required']) {
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                new AddDeleteCheckboxListener($this->storage, $options['delete_label'], $options['translation_domain'])
            );
            $builder->addEventListener(FormEvents::PRE_SUBMIT, new ClearBase64OnDeleteListener());
            $builder->addEventListener(FormEvents::POST_SUBMIT, new DeleteFileListener($this->handler));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('allow_delete', true)
            ->setAllowedTypes('allow_delete', ['bool'])

            ->setDefault('delete_label', 'btn_delete')
            ->setAllowedTypes('delete_label', ['string'])

            ->setDefault(
                'aspect_ratios',
                [
                    '16:9' => new AspectRatio(1.78, 'aspect_ratio.16:9'),
                    '4:3' => new AspectRatio(1.33, 'aspect_ratio.4:3'),
                    '1' => new AspectRatio(1, 'aspect_ratio.1'),
                    '2:3' => new AspectRatio(0.66, 'aspect_ratio.2:3'),
                    'nan' => new AspectRatio(null, 'aspect_ratio.nan', true),
                ]
            )
            ->setAllowedTypes('aspect_ratios', ['array'])
            ->setInfo('aspect_ratios', 'A list of aspect ratio to apply when resizing an image.')

            ->setDefault('cropper_options', ['autoCropArea' => 1])
            ->setAllowedTypes('cropper_options', ['array'])
            ->setInfo('cropper_options', 'A list of options supported by cropper.')

            ->setDefault('max_width', 320)
            ->setAllowedTypes('max_width', ['int'])
            ->setInfo('max_width', 'The max width of the cropped image send to server.')

            ->setDefault('max_height', 180)
            ->setAllowedTypes('max_height', ['int'])
            ->setInfo('max_height', 'The max height of the cropped image send to server.')

            ->setDefault(
                'preview_width',
                static function (Options $options): string {
                    \assert(\is_int($options['max_width']));

                    return "{$options['max_width']}px";
                }
            )
            ->setAllowedTypes('preview_width', ['string'])
            ->setInfo(
                'preview_width',
                'The max width to use when displaying the image preview. Can be in px, % or other css value.'
            )

            ->setDefault(
                'preview_height',
                static function (Options $options): string {
                    \assert(\is_int($options['max_height']));

                    return "{$options['max_height']}px";
                }
            )
            ->setAllowedTypes('preview_height', ['string'])
            ->setInfo(
                'preview_height',
                'The max height to use when displaying the image preview. Can be in px, % or other css value.'
            )

            ->setDefault('upload_button_class', '')
            ->setAllowedTypes('upload_button_class', ['string'])
            ->setInfo('upload_button_class', 'CSS class of the "upload" button.')

            ->setDefault('cancel_button_class', '')
            ->setAllowedTypes('cancel_button_class', ['string'])
            ->setInfo('cancel_button_class', 'CSS class of the "cancel" button.')

            ->setDefault('save_button_class', '')
            ->setAllowedTypes('save_button_class', ['string'])
            ->setInfo('save_button_class', 'CSS class of the "save" button.')

            ->setDefault('download_uri', null)
            ->setAllowedTypes('download_uri', ['string', 'null'])
            ->setInfo('download_uri', 'The path where the image is located.')

            ->setDefault('show_image', null)
            ->setAllowedTypes('show_image', ['bool', 'null'])
            ->setInfo('show_image', 'Whether the image should be rendered in the form or not.')

            ->setDefault('download_link', true)
            ->setAllowedTypes('download_link', ['bool'])
            ->setInfo('download_link', 'Whether the image should be rendered in the form or not.')
            ->setDeprecated(
                'download_link',
                'presta/image-bundle',
                '2.6.0',
                'The option "download_link" is deprecated, use "show_image" instead.',
            )

            ->setDefault('file_upload_enabled', true)
            ->setAllowedTypes('file_upload_enabled', ['bool'])
            ->setInfo('file_upload_enabled', 'Whether to enable the file upload widget or not.')

            ->setDefault('remote_url_enabled', true)
            ->setAllowedTypes('remote_url_enabled', ['bool'])
            ->setInfo('remote_url_enabled', 'Whether to enable the remote url widget or not.')

            ->setDefault('rotation_enabled', false)
            ->setAllowedTypes('rotation_enabled', ['bool'])
            ->setInfo('rotation_enabled', 'Whether to enable the rotation or not.')

            ->setDefault('translation_domain', 'PrestaImageBundle')
            ->setAllowedTypes('translation_domain', ['string'])

            ->setDefault('upload_mimetype', 'image/png')
            ->setAllowedTypes('upload_mimetype', ['string'])
            ->setInfo(
                'upload_mimetype',
                'Format of the image to be uploaded. Note: If the chosen mimetype is not supported by the browser, '
                    . 'it will silently fall back to `image/png`.'
            )

            // default value: https://developer.mozilla.org/de/docs/Web/API/HTMLCanvasElement/toDataURL
            ->setDefault('upload_quality', 0.92)
            ->setAllowedTypes('upload_quality', ['float'])
            ->setInfo('upload_quality', 'Quality (0..1) of uploaded image for lossy imageformats (eg. `image/jpeg`).')

            ->setDefault('error_bubbling', false)
            ->setAllowedTypes('error_bubbling', ['bool'])
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

        $showImage = $options['show_image'] ?? $options['download_link'];
        if ($showImage && $downloadUri = $options['download_uri'] ?? $this->generateDownloadUri($form)) {
            $view->vars['download_uri'] = $downloadUri;
        }
    }

    private function generateDownloadUri(FormInterface $form): ?string
    {
        $parent = $form->getParent();
        if (null === $parent) {
            throw new \RuntimeException(get_class($form) . ' should not be used as root form.');
        }

        $data = $parent->getData();
        if (null === $data) {
            return null;
        }

        \assert(\is_array($data) || \is_object($data));

        return $this->storage->resolveUri($data, $form->getName());
    }
}
