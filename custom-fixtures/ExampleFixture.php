<?php declare(strict_types=1);

namespace Wolnosciowiec\CustomFixtures;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Fixtures\FixtureInterface;

class ExampleFixture implements FixtureInterface
{
    public function fixResponse(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('X-Message', 'Hello');
    }
}
