<?php

namespace Presta\ImageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PrestaImageBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
