<?php

namespace Presta\ImageBundle\Helper;

trait Base64Helper
{
    private function contentToBase64(string $content): string
    {
        $imageData = file_get_contents($content);
        $imageInfo = getimagesizefromstring($imageData);
        $mimeType = $imageInfo['mime'] ?? 'image/png';

        $base64 = base64_encode($imageData);

        return "data:$mimeType;base64,$base64";
    }
}
