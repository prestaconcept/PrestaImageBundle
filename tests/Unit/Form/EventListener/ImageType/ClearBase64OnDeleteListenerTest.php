<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Tests\Unit\Form\EventListener\ImageType;

use Presta\ImageBundle\Form\EventListener\ImageType\ClearBase64OnDeleteListener;
use Presta\ImageBundle\Form\Type\ImageType;
use Presta\ImageBundle\Tests\Unit\Form\ImageTypeTestCase;
use Symfony\Component\Form\FormEvent;

final class ClearBase64OnDeleteListenerTest extends ImageTypeTestCase
{
    /**
     * @dataProvider deletableSubmittedData
     */
    public function testShouldClearTheSubmittedBase64DataIfSubmitted(array $submittedData): void
    {
        $form = $this->factory->create();
        $event = new FormEvent($form, array_merge($submittedData, ['base64' => 'foo']));

        $listener = new ClearBase64OnDeleteListener();
        $listener($event);

        $data = $event->getData();
        \assert(\is_array($data));

        $this->assertArrayHasKey('base64', $data);
        $this->assertNull($data['base64']);
    }

    /**
     * @dataProvider notDeletableSubmittedData
     */
    public function testShouldNotClearTheSubmittedBase64DataIfSubmitted(array $submittedData): void
    {
        $form = $this->factory->create();
        $event = new FormEvent($form, array_merge($submittedData, ['base64' => 'foo']));

        $listener = new ClearBase64OnDeleteListener();
        $listener($event);

        $data = $event->getData();
        \assert(\is_array($data));

        $this->assertSame('foo', $data['base64']);
    }

    public function testShouldEndUpWithNullBase64DataIfSubmittedWithNullData(): void
    {
        $form = $this->factory->create()->add('image', ImageType::class, self::ALLOW_DELETE_OPTIONS);
        $event = new FormEvent($form, null);

        $listener = new ClearBase64OnDeleteListener();
        $listener($event);

        $this->assertNull($event->getData());
    }

    public function deletableSubmittedData(): iterable
    {
        yield 'the "delete" checkbox checked' => [
            ['delete' => true],
        ];
    }

    public function notDeletableSubmittedData(): iterable
    {
        yield 'no "delete" checkbox data' => [[]];
        yield 'the "delete" checkbox not checked' => [
            ['delete' => false],
        ];
    }
}
