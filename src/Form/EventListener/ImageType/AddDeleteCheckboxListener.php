<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Form\EventListener\ImageType;

use Presta\ImageBundle\Exception\UnexpectedTypeException;
use Presta\ImageBundle\Form\EventListener\FormEventListener;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Vich\UploaderBundle\Storage\StorageInterface;

final class AddDeleteCheckboxListener implements FormEventListener
{
    private StorageInterface $storage;
    private string $deleteLabel;
    private string $translationDomain;

    public function __construct(StorageInterface $storage, string $deleteLabel, string $translationDomain)
    {
        $this->storage = $storage;
        $this->deleteLabel = $deleteLabel;
        $this->translationDomain = $translationDomain;
    }

    public function __invoke(FormEvent $event): void
    {
        $form = $event->getForm();

        $parent = $form->getParent();
        if (null === $parent) {
            throw new \RuntimeException(get_class($form) . ' should not be used as root form.');
        }

        $data = $parent->getData();
        if (null === $data) {
            return;
        }

        if (!\is_object($data)) {
            throw new UnexpectedTypeException($data, 'object');
        }

        if (null === $this->storage->resolvePath($data, $form->getName())) {
            return;
        }

        $form->add(
            'delete',
            CheckboxType::class,
            [
                'label' => $this->deleteLabel,
                'required' => false,
                'mapped' => false,
                'translation_domain' => $this->translationDomain,
            ]
        );
    }
}
