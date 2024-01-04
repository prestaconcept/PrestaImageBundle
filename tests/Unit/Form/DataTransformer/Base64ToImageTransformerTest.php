<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Tests\Unit\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Presta\ImageBundle\Form\DataTransformer\Base64ToImageTransformer;
use Symfony\Component\HttpFoundation\File\File;

final class Base64ToImageTransformerTest extends TestCase
{
    /**
     * @dataProvider validOriginalValues
     *
     * @param mixed $value
     */
    public function testTransformingAValidValue(string $mimeType, $value): void
    {
        $transformer = new Base64ToImageTransformer();
        $transformedValue = $transformer->transform($value);

        self::assertIsArray($transformedValue);
        self::assertArrayHasKey('base64', $transformedValue);
        self::assertStringStartsWith("data:$mimeType;base64,", $transformedValue['base64']);
    }

    /**
     * @dataProvider invalidOriginalValues
     *
     * @param mixed $expected
     * @param mixed $value
     */
    public function testTransformingAnInvalidValue($expected, $value): void
    {
        $transformer = new Base64ToImageTransformer();

        self::assertSame($expected, $transformer->transform($value));
    }

    /**
     * @dataProvider invalidTransformedValues
     *
     * @param mixed $expected
     * @param mixed $value
     */
    public function testReverseTransform($expected, $value): void
    {
        $transformer = new Base64ToImageTransformer();

        self::assertSame($expected, $transformer->reverseTransform($value));
    }

    public function validOriginalValues(): iterable
    {
        yield 'a ' . File::class . ' object related to a file on the filesystem should return a base64' => [
            'image/jpeg',
            new File(dirname(__DIR__) . '/../../App/Resources/images/A.jpg'),
        ];
    }

    public function invalidOriginalValues(): iterable
    {
        $fileClass = File::class;

        yield 'an empty value (null) should return an empty (null) base64' => [['base64' => null], null];
        yield "a value different from $fileClass should return an empty (null) base64" => [
            ['base64' => null],
            new \stdClass(),
        ];
        yield "a $fileClass object not related to a file on the filesystem should return an empty (null) base64" => [
            ['base64' => null],
            new File('/tmp/foo.png', false),
        ];
    }

    public function invalidTransformedValues(): iterable
    {
        yield 'an empty value (null) should return null' => [null, null];
        yield 'a value different from an array should return null' => [null, null];
        yield 'an array without a "base64" key should return null' => [null, null];
    }
}
