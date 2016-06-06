<?php

namespace Presta\ImageBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
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
        if (!$value instanceof UploadedFile) {
            return null;
        }

        return file_get_contents($value->getRealPath());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value['base64']) {
            return null;
        }

        $base64 = str_replace('data:image/png;base64,', '', $value['base64']);

        $filePath = tempnam(sys_get_temp_dir(), 'UploadedFile');
        $file = fopen($filePath, 'w');
        stream_filter_append($file, 'convert.base64-decode');
        fwrite($file, $base64);
        $meta_data = stream_get_meta_data($file);
        $path = $meta_data['uri'];
        fclose($file);

        // Force "test" parameters to true to bypass http file validation (as the file isn't a "real" uploaded file)
        return new UploadedFile($path, uniqid(), null, null, null, true);
    }
}
