<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Tests\Unit\Form\EventListener\ImageType;

use Presta\ImageBundle\Exception\UnexpectedTypeException;
use Presta\ImageBundle\Form\EventListener\ImageType\DeleteFileListener;
use Presta\ImageBundle\Form\Type\ImageType;
use Presta\ImageBundle\Tests\App\Model\Book;
use Presta\ImageBundle\Tests\Unit\Form\ImageTypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormEvent;
use Vich\UploaderBundle\Mapping\PropertyMapping;

final class DeleteFileListenerTest extends ImageTypeTestCase
{
    /**
     * @dataProvider deletableConfig
     */
    public function testShouldTriggerRemovingTheFileFromTheFilesystemIfSubmitted(Book $data, bool $delete): void
    {
        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->with($data, 'image')
            ->willReturn($data->imageName)
        ;

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($data, $this->isInstanceOf(PropertyMapping::class))
        ;

        $form = $this->factory
            ->create(FormType::class, $data)
            ->add('image', ImageType::class, self::ALLOW_DELETE_OPTIONS)
        ;

        $form->get('image')->get('delete')->setData($delete);

        $listener = new DeleteFileListener($this->createUploadHandler());
        $listener(new FormEvent($form->get('image'), $form->get('image')->getViewData()));
    }

    /**
     * @dataProvider notDeletableConfig
     *
     * @param mixed $data
     */
    public function testShouldNotTriggerRemovingTheFileFromTheFilesystemIfSubmitted(
        $data,
        array $options,
        bool $delete = null
    ): void {
        $this->storage->expects($this->never())->method('remove');

        $form = $this->factory
            ->create(FormType::class, $data)
            ->add('image', ImageType::class, $options)
        ;

        if ($form->get('image')->has('delete')) {
            $form->get('image')->get('delete')->setData($delete);
        }

        $listener = new DeleteFileListener($this->createUploadHandler());
        $listener(new FormEvent($form->get('image'), $form->get('image')->getViewData()));
    }

    public function testShouldCauseAnExceptionIfCreatedAsRootForm(): void
    {
        $this->expectException(\RuntimeException::class);

        $form = $this->factory->create();

        $listener = new DeleteFileListener($this->createUploadHandler());
        $listener(new FormEvent($form, $form->getData()));
    }

    public function testShouldCauseAnExceptionIfCreatedWithAnArrayAsData(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $form = $this->factory->create(FormType::class, [])->add('image');

        $listener = new DeleteFileListener($this->createUploadHandler());
        $listener(new FormEvent($form->get('image'), $form->get('image')->getViewData()));
    }

    public function deletableConfig(): iterable
    {
        yield 'the "delete" checkbox checked when created with an object related to an existing file' => [
            Book::withFile('/tmp/foo.png'),
            true,
        ];
    }

    public function notDeletableConfig(): iterable
    {
        yield 'no data (null)' => [null, self::ALLOW_DELETE_OPTIONS];
        yield 'no "delete" checkbox' => [Book::withoutFile(), ['allow_delete' => false]];
        yield 'the "delete" checkbox not checked' => [Book::withoutFile(), self::ALLOW_DELETE_OPTIONS, false];
    }
}
