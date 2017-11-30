<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Controllers\PassThroughController;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;

/**
 * Runs the application
 */
class ApplicationMiddleware
{
    /**
     * @var PassThroughController $controller
     */
    private $controller;

    /**
     * @param PassThroughController $controller
     */
    public function __construct(PassThroughController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @param ForwardableRequest $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @throws \Exception
     * @return \GuzzleHttp\Psr7\Response
     */
    public function __invoke(ForwardableRequest $request, ResponseInterface $response, callable $next)
    {
        return $next($request, $this->controller->executeAction($request));
    }
}
