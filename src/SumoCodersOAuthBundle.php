<?php

namespace SumoCoders\OAuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SumoCodersOAuthBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
