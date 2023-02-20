<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Tests\Unit\Form\EventListener\ImageType;

use Presta\ImageBundle\Exception\UnexpectedTypeException;
use Presta\ImageBundle\Form\EventListener\ImageType\AddDeleteCheckboxListener;
use Presta\ImageBundle\Form\Type\ImageType;
use Presta\ImageBundle\Tests\App\Model\Book;
use Presta\ImageBundle\Tests\Unit\Form\ImageTypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormEvent;

final class AddDeleteCheckboxListenerTest extends ImageTypeTestCase
{
    /**
     * @dataProvider deletableData
     */
    public function testAnImageTypeChildShouldHaveADeleteCheckboxIfCreated(Book $data): void
    {
        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->with($data, 'image')
            ->willReturn('/tmp/foo.png')
        ;

        $form = $this->factory->create(FormType::class, $data)->add('image', ImageType::class);

        $listener = new AddDeleteCheckboxListener($this->storage, 'foo', 'messages');
        $listener(new FormEvent($form->get('image'), $form->get('image')->getData()));

        $this->assertTrue($form->get('image')->has('delete'));
    }

    /**
     * @dataProvider notDeletableData
     *
     * @param mixed $data
     */
    public function testAnImageTypeChildShouldNotHaveADeleteCheckboxIfCreated($data): void
    {
        $this->storage->method('resolvePath')->willReturn(null);

        $form = $this->factory->create(FormType::class, $data)->add('image', ImageType::class);

        $listener = new AddDeleteCheckboxListener($this->storage, 'foo', 'messages');
        $listener(new FormEvent($form->get('image'), $form->get('image')->getData()));

        $this->assertFalse($form->get('image')->has('delete'));
    }

    public function testShouldCauseAnExceptionIfCreatedAsRootForm(): void
    {
        $this->expectException(\RuntimeException::class);

        $form = $this->factory->create(ImageType::class);

        $listener = new AddDeleteCheckboxListener($this->storage, 'foo', 'messages');
        $listener(new FormEvent($form, $form->getData()));
    }

    public function testShouldCauseAnExceptionIfCreatedWithAnArrayAsData(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $form = $this->factory->create(FormType::class, [])->add('image', ImageType::class);

        $listener = new AddDeleteCheckboxListener($this->storage, 'foo', 'messages');
        $listener(new FormEvent($form->get('image'), $form->get('image')->getData()));
    }

    public function deletableData(): iterable
    {
        yield 'an object related to a file stored on the filesystem' => [Book::withoutFile()];
    }

    public function notDeletableData(): iterable
    {
        yield 'no data (null)' => [null];
        yield 'an object not related to a file stored on the filesystem' => [Book::withoutFile()];
    }
}
