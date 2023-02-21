<?php

namespace Presta\ImageBundle\Helper;

trait Base64Helper
{
    private function contentToBase64(string $filename): string
    {
        $imageData = \file_get_contents($filename);
        // @codeCoverageIgnoreStart
        if (!\is_string($imageData)) {
            throw new \RuntimeException('Could not read the file\'s content.');
        }
        // @codeCoverageIgnoreEnd

        $imageInfo = getimagesizefromstring($imageData);
        if (false === $imageInfo || !array_key_exists('mime', $imageInfo)) {
            throw new \RuntimeException('The file does not seem to be an image.');
        }

        $base64 = base64_encode($imageData);

        return "data:{$imageInfo['mime']};base64,$base64";
    }
}
