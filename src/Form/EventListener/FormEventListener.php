<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;

interface FormEventListener
{
    public function __invoke(FormEvent $event): void;
}
