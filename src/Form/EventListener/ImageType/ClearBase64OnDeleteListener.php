<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Form\EventListener\ImageType;

use Presta\ImageBundle\Form\EventListener\FormEventListener;
use Symfony\Component\Form\FormEvent;

final class ClearBase64OnDeleteListener implements FormEventListener
{
    public function __invoke(FormEvent $event): void
    {
        $data = $event->getData();
        if (!\is_array($data)) {
            return;
        }

        if (!($data['delete'] ?? false)) {
            return;
        }

        $data['base64'] = null;

        $event->setData($data);
    }
}
