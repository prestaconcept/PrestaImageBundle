<?php

namespace Presta\ImageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Benoit Jouhaud <bjouhaud@prestaconcept.net>
 */
class DefaultController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function urlToBase64Action(Request $request)
    {
        $image = file_get_contents($request->request->get('url'));
        $imageInfo = getimagesizefromstring($image);
        $mimeType = $imageInfo !== false ? $imageInfo['mime'] : 'image/png';
        $base64 = sprintf('data:' . $mimeType . ';base64,%s', base64_encode($image));

        return new JsonResponse([
            'base64' => $base64,
        ]);
    }
}
