<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Controllers;

use Psr\Http\Message\RequestInterface;

abstract class BaseController
{
    protected function hasDisabledExternalProxy(RequestInterface $request): bool
    {
        $headerValue = $request->getHeader('ww-no-external-proxy')[0] ?? '';

        return $this->getBooleanValue($headerValue);
    }

    private function getBooleanValue(string $value)
    {
        return in_array($value, ['1', 'true'], true);
    }
}
