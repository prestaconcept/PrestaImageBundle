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
        $this->storage->method('resolvePath')->willReturn('/tmp/foo.png');

        $options = ['allow_delete' => $allowDelete, 'required' => $required];
        $data = Book::withoutFile();

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
        $options = ['allow_delete' => $allowDelete, 'required' => $required];
        $data = Book::withFile('/tmp/foo.png');

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
}
