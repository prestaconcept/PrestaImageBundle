<?php

namespace Presta\ImageBundle\Form\DataTransformer;

use Presta\ImageBundle\Helper\Base64Helper;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Base64ToImageTransformer implements DataTransformerInterface
{
    use Base64Helper;

    public function transform($value): array
    {
        if (!$value instanceof File || false === $value->getRealPath()) {
            return ['base64' => null];
        }

        return ['base64' => $this->contentToBase64($value->getRealPath())];
    }

    public function reverseTransform($value): ?UploadedFile
    {
        if (!\is_array($value) || !($value['base64'] ?? null)) {
            return null;
        }

        $prefixLength = \strpos($value['base64'], 'base64,') + 7;
        $base64 = substr($value['base64'], $prefixLength);

        $filepath = tempnam(sys_get_temp_dir(), 'UploadedFile');
        if (!\is_string($filepath)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Could not generate a valid temporary file path.');
            // @codeCoverageIgnoreEnd
        }

        $file = fopen($filepath, 'w');
        if (!\is_resource($file)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException("Could not open the \"$filepath\" file in \"w\" mode.");
            // @codeCoverageIgnoreEnd
        }

        stream_filter_append($file, 'convert.base64-decode');
        fwrite($file, $base64);

        $metadata = stream_get_meta_data($file);
        $filename = $metadata['uri'];

        fclose($file);

        if (!\is_string($filename)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Could not get the generated file uri from metadata.');
            // @codeCoverageIgnoreEnd
        }

        $mimeType = mime_content_type($filename);
        if (!\is_string($mimeType)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Could not guess the image mime type.');
            // @codeCoverageIgnoreEnd
        }

        $extension = str_replace('image/', '', $mimeType);

        return new UploadedFile($filename, uniqid() . ".$extension", $mimeType, null, true);
    }
}
