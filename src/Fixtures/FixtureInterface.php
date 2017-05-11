<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Fixtures;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface FixtureInterface
{
    /**
     * Take response, fix, and output
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function fixResponse(RequestInterface $request, ResponseInterface $response): ResponseInterface;
}
