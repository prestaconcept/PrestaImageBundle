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
        if (!isset($value['base64']) || !$value['base64']) {
            return null;
        }

        $prefixLength = strpos($value['base64'], 'base64,') + 7;
        $base64 = substr($value['base64'], $prefixLength);

        $filePath = tempnam(sys_get_temp_dir(), 'UploadedFile');
        $file = fopen($filePath, 'w');
        stream_filter_append($file, 'convert.base64-decode');
        fwrite($file, $base64);
        $metadata = stream_get_meta_data($file);
        $path = $metadata['uri'];
        fclose($file);
        $mimeType = mime_content_type($path);
        $extension = str_replace('image/', '', $mimeType);

        return new UploadedFile($path, uniqid() . '.' . $extension, $mimeType, null, true);
    }
}
