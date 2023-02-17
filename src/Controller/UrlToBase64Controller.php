<?php

namespace Presta\ImageBundle\Controller;

use Presta\ImageBundle\Helper\Base64Helper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UrlToBase64Controller
{
    use Base64Helper;

    public function __invoke(Request $request): JsonResponse
    {
        if (!$request->request->has('url')) {
            throw new \RuntimeException('Parameter "url" is required.');
        }

        return new JsonResponse(
            [
                'base64' => $this->contentToBase64($request->request->getAlpha('url')),
            ]
        );
    }
}
