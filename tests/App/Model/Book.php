<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Tests\App\Model;

use Symfony\Component\HttpFoundation\File\File;

final class Book
{
    public ?File $image = null;
    public ?string $imageName = null;

    private function __construct()
    {
    }

    public static function withoutFile(): self
    {
        return new self();
    }

    public static function withFile(string $imageName): self
    {
        $book = new self();
        $book->imageName = $imageName;

        return $book;
    }
}
