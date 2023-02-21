<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Presta\ImageBundle\Controller\UrlToBase64Controller;
use Presta\ImageBundle\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UrlToBase64ControllerTest extends TestCase
{
    public function testShouldCauseAnExceptionIfTheRequestDoesNotHaveAUrlParameterInItsBody(): void
    {
        $this->expectException(\RuntimeException::class);

        $controller = new UrlToBase64Controller();
        $controller(Request::create('/url_to_base64', Request::METHOD_POST));
    }

    public function testShouldCauseAnExceptionIfTheUrlParameterIsNotAString(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $controller = new UrlToBase64Controller();
        $controller(Request::create('/url_to_base64', Request::METHOD_POST, ['url' => false]));
    }

    /**
     * Note: the "url" parameter should be a valid url, but technically nothing prevents a user to path a valid file
     * path to get the file's contents.
     * Restricting the reading to images prevents a major security issue.
     */
    public function testShouldCauseAnExceptionIfTheUrlDoesNotReferenceAnImage(): void
    {
        $this->expectException(\RuntimeException::class);

        $controller = new UrlToBase64Controller();
        $controller(
            Request::create(
                '/url_to_base64',
                Request::METHOD_POST,
                ['url' => dirname(__DIR__) . '/../App/Resources/files/dummy.pdf']
            )
        );
    }

    public function testShouldReturnAJsonResponseContainingTheImageBase64Representation(): void
    {
        $controller = new UrlToBase64Controller();
        $response = $controller(
            Request::create(
                '/url_to_base64',
                Request::METHOD_POST,
                ['url' => dirname(__DIR__) . '/../App/Resources/images/A.jpg']
            )
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $content = $response->getContent();
        self::assertIsString($content);

        $data = json_decode($content, true);
        self::assertIsArray($data);

        self::assertArrayHasKey('base64', $data);
        self::assertStringStartsWith('data:image/jpeg;base64', $data['base64']);
    }
}
