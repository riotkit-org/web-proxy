<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Fixtures;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * NotFoundTo500
 * =============
 *
 * Converts any 404 not found to 500 error
 * Helpful for load balancer configuration
 */
class NotFoundTo500 implements FixtureInterface
{
    public function fixResponse(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($response->getStatusCode() === 404) {
            $response = $response->withStatus(500, 'Bad output status code');
        }

        return $response;
    }
}
