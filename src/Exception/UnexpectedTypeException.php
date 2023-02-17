<?php

declare(strict_types=1);

namespace Presta\ImageBundle\Exception;

final class UnexpectedTypeException extends \RuntimeException
{
    /**
     * @param mixed $value
     */
    public function __construct($value, string $expectedType)
    {
        $actualType = get_debug_type($value);

        parent::__construct("Expected argument of type \"$expectedType\", \"$actualType\" given");
    }
}
