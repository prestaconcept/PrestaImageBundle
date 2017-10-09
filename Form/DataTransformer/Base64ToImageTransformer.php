<?php

namespace Presta\ImageBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Thomas Courthial <tcourthial@prestaconcept.net>
 */
class Base64ToImageTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value instanceof File || !$value->getRealPath()) {
            return ['base64' => null];
        }

        $imageData = file_get_contents($value->getRealPath());
        $imageInfo = getimagesizefromstring($imageData);
        $mimeType = $imageInfo !== false ? $imageInfo['mime'] : 'image/png';
        return ['base64' => 'data:' . $mimeType . ';base64,' . base64_encode($imageData)];
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!isset($value['base64']) || !$value['base64']) {
            return null;
        }

        $prefixLenght = strpos($value['base64'], 'base64,') + 7;
        $base64 = substr($value['base64'], $prefixLenght);

        $filePath = tempnam(sys_get_temp_dir(), 'UploadedFile');
        $file = fopen($filePath, 'w');
        stream_filter_append($file, 'convert.base64-decode');
        fwrite($file, $base64);
        $meta_data = stream_get_meta_data($file);
        $path = $meta_data['uri'];
        fclose($file);

        // Force "test" parameters to true to bypass http file validation (as the file isn't a "real" uploaded file)
        // TODO: Should we get and define the mimeType here?
        // TODO: Should we maybe also send and set the originalFilename?
        return new UploadedFile($path, uniqid(), null, null, null, true);
    }
}
