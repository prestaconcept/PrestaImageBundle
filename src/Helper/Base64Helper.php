<?php

namespace Presta\ImageBundle\Helper;

trait Base64Helper
{
    private function contentToBase64(string $filename): string
    {
        $imageData = \file_get_contents($filename);
        if (!\is_string($imageData)) {
            throw new \RuntimeException('Could not read the file\'s content.');
        }

        $imageInfo = getimagesizefromstring($imageData);
        $mimeType = $imageInfo['mime'] ?? 'image/png';

        if (false === \strpos($mimeType, 'image/')) {
            throw new \RuntimeException('The file does not seem to be an image.');
        }

        $base64 = base64_encode($imageData);

        return "data:$mimeType;base64,$base64";
    }
}
