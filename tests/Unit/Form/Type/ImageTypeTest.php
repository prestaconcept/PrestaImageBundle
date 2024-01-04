<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Tests\Unit\Form\Type;

use Presta\ImageBundle\Form\Type\ImageType;
use Presta\ImageBundle\Tests\App\Model\Book;
use Presta\ImageBundle\Tests\Unit\Form\ImageTypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Vich\UploaderBundle\Mapping\PropertyMapping;

final class ImageTypeTest extends ImageTypeTestCase
{
    /**
     * @dataProvider deletableOptions
     */
    public function testShouldHaveADeleteCheckboxIfCreated(bool $allowDelete, bool $required): void
    {
        $data = Book::illustrated('foo.png');
        $options = ['allow_delete' => $allowDelete, 'required' => $required];

        \assert(null !== $data->image);

        $this->storage->method('resolvePath')->willReturn($data->image->getPathname());

        $form = $this->factory->create(FormType::class, $data)->add('image', ImageType::class, $options);

        $this->assertTrue($form->get('image')->has('delete'));
    }

    /**
     * @dataProvider notDeletableOptions
     */
    public function testShouldNotHaveADeleteCheckboxIfCreated(bool $allowDelete, bool $required): void
    {
        $options = ['allow_delete' => $allowDelete, 'required' => $required];

        $form = $this->factory->create()->add('image', ImageType::class, $options);

        $this->assertFalse($form->get('image')->has('delete'));
    }

    /**
     * @dataProvider deletableOptions
     */
    public function testShouldClearTheBase64SubmittedDataIfCreated(bool $allowDelete, bool $required): void
    {
        $options = ['allow_delete' => $allowDelete, 'required' => $required];

        $form = $this->factory->create()->add('image', ImageType::class, $options);
        $form->submit(['image' => ['delete' => true, 'base64' => 'foo']]);

        $this->assertTrue($form->isSynchronized());
        $this->assertNull($form->get('image')->get('base64')->getData());
    }

    /**
     * @dataProvider notDeletableOptions
     */
    public function testShouldNotClearTheBase64SubmittedDataIfCreated(bool $allowDelete, bool $required): void
    {
        $options = ['allow_delete' => $allowDelete, 'required' => $required];

        $form = $this->factory->create()->add('image', ImageType::class, $options);
        $form->submit(['image' => ['delete' => true, 'base64' => 'foo']]);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('foo', $form->get('image')->get('base64')->getData());
    }

    /**
     * @dataProvider deletableOptions
     */
    public function testShouldRemoveTheFileFromTheFilesystemIfCreated(bool $allowDelete, bool $required): void
    {
        $data = Book::illustrated('foo.png');
        $options = ['allow_delete' => $allowDelete, 'required' => $required];

        \assert(null !== $data->image);

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->with($data, 'image')
            ->willReturn($data->image->getPathname())
        ;

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($data, $this->isInstanceOf(PropertyMapping::class))
        ;

        $form = $this->factory->create(FormType::class, $data)->add('image', ImageType::class, $options);
        $form->submit(['image' => ['delete' => true]]);

        $this->assertTrue($form->isSynchronized());
    }

    /**
     * @dataProvider notDeletableOptions
     */
    public function testShouldNotRemoveTheFileFromTheFilesystemIfCreated(bool $allowDelete, bool $required): void
    {
        $options = ['allow_delete' => $allowDelete, 'required' => $required];

        $this->storage->expects($this->never())->method('remove');

        $form = $this->factory->create()->add('image', ImageType::class, $options);
        $form->submit([]);

        $this->assertTrue($form->isSynchronized());
    }

    /**
     * @dataProvider downloadableConfig
     */
    public function testShouldAddDownloadUriToTheViewVars(Book $data, bool $showImage): void
    {
        \assert(null !== $data->imageName);

        $expected = "/book/$data->imageName";
        $options = ['show_image' => $showImage];

        $form = $this->factory
            ->create(FormType::class, $data)
            ->add('image', ImageType::class, $options)
        ;

        $this->storage
            ->expects($this->once())
            ->method('resolveUri')
            ->with($data, 'image')
            ->willReturn($expected)
        ;

        $view = $form->createView();

        self::assertArrayHasKey('download_uri', $view->children['image']->vars);
        self::assertSame($expected, $view->children['image']->vars['download_uri']);
    }

    /**
     * @dataProvider notDownloadableConfig
     */
    public function testShouldNotAddDownloadUriToTheViewVars(?Book $data, array $options): void
    {
        $this->storage->expects($this->never())->method('resolveUri');

        $form = $this->factory->create(FormType::class, $data)->add('image', ImageType::class, $options);

        $view = $form->createView();

        self::assertArrayNotHasKey('download_uri', $view->children['image']->vars);
    }

    public function testShouldCauseAnExceptionIfCreatedAsRootForm(): void
    {
        $this->expectException(\RuntimeException::class);

        $form = $this->factory->create(ImageType::class);

        $form->createView();
    }

    public function deletableOptions(): iterable
    {
        yield 'option "allow_delete" set to true and option "required" set to false' => [true, false];
    }

    public function notDeletableOptions(): iterable
    {
        yield 'option "allow_delete" set to false and option "required" set to false' => [false, false];
        yield 'option "allow_delete" set to false and option "required" set to true' => [false, true];
        yield 'option "allow_delete" set to true and option "required" set to true' => [true, true];
    }

    public function downloadableConfig(): iterable
    {
        yield 'the "show_image" option set to true when created with an object related to an existing file' => [
            Book::illustrated('foo.png'),
            true,
        ];
    }

    public function notDownloadableConfig(): iterable
    {
        yield 'no data (null)' => [null, self::ALLOW_DELETE_OPTIONS];
        yield 'no download uri' => [Book::illustrated('foo.png'), ['show_image' => false]];
    }
}
