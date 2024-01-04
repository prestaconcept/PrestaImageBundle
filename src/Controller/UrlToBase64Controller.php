<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Controller;

use Presta\ImageBundle\Exception\UnexpectedTypeException;
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

        $url = $request->request->get('url');
        if (!\is_string($url)) {
            throw new UnexpectedTypeException($url, 'string');
        }

        return new JsonResponse(['base64' => $this->contentToBase64($url)]);
    }
}
