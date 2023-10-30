<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Tests\Unit\Form;

use Metadata\AdvancedMetadataFactoryInterface;
use Metadata\ClassHierarchyMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use Presta\ImageBundle\Form\Type\ImageType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Mapping\PropertyMappingResolver;
use Vich\UploaderBundle\Metadata\ClassMetadata;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Storage\StorageInterface;

abstract class ImageTypeTestCase extends TypeTestCase
{
    protected const ALLOW_DELETE_OPTIONS = ['allow_delete' => true, 'required' => false];
    protected const ALLOW_DOWNLOAD_OPTIONS = ['download_link' => true];

    /**
     * @var MockObject&StorageInterface
     */
    protected MockObject $storage;

    /**
     * @var MockObject&FileInjectorInterface
     */
    private MockObject $fileInjector;

    /**
     * @var MockObject&EventDispatcherInterface
     */
    private MockObject $eventDispatcher;

    /**
     * @var MockObject&ContainerInterface
     */
    private MockObject $container;

    /**
     * @var MockObject&AdvancedMetadataFactoryInterface
     */
    private MockObject $advancedMetadataFactory;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->fileInjector = $this->createMock(FileInjectorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->advancedMetadataFactory = $this->createMock(AdvancedMetadataFactoryInterface::class);

        $classMetadata = new ClassMetadata('anonymous');
        $classMetadata->fields['image'] = ['mapping' => 'default', 'name' => 'imageName'];

        $metadata = new ClassHierarchyMetadata();
        $metadata->addClassMetadata($classMetadata);

        $this->advancedMetadataFactory->method('getMetadataForClass')->willReturn($metadata);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        $type = new ImageType($this->storage, $this->createUploadHandler());

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    protected function createUploadHandler(): UploadHandler
    {
        if (\class_exists('Vich\UploaderBundle\Mapping\PropertyMappingResolver')) {
            $factory = new PropertyMappingFactory(
                new MetadataReader($this->advancedMetadataFactory),
                new PropertyMappingResolver($this->container, ['default' => []])
            );
        } else {
            $factory = new PropertyMappingFactory(
                $this->container,
                new MetadataReader($this->advancedMetadataFactory),
                ['default' => []]
            );
        }

        return new UploadHandler($factory, $this->storage, $this->fileInjector, $this->eventDispatcher);
    }
}
