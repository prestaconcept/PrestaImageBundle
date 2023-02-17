<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Form\EventListener\ImageType;

use Presta\ImageBundle\Exception\UnexpectedTypeException;
use Presta\ImageBundle\Form\EventListener\FormEventListener;
use Symfony\Component\Form\FormEvent;
use Vich\UploaderBundle\Handler\UploadHandler;

final class DeleteFileListener implements FormEventListener
{
    private UploadHandler $handler;

    public function __construct(UploadHandler $handler)
    {
        $this->handler = $handler;
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

        if (!$form->has('delete') || !$form->get('delete')->getData()) {
            return;
        }

        $this->handler->remove($data, $form->getName());
    }
}
