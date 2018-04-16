<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Controllers\PassThroughController;
use Wolnosciowiec\WebProxy\Controllers\ProxySelectorController;
use Wolnosciowiec\WebProxy\Controllers\RenderController;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\InputParams;

/**
 * Runs the application
 */
class ApplicationMiddleware
{
    /**
     * @var PassThroughController $passThroughController
     */
    private $passThroughController;

    /**
     * @var ProxySelectorController $selectorController
     */
    private $selectorController;

    /**
     * @var RenderController $renderController
     */
    private $renderController;

    public function __construct(
        PassThroughController   $passThroughController,
        ProxySelectorController $selectorController,
        RenderController        $renderController)
    {
        $this->passThroughController = $passThroughController;
        $this->selectorController    = $selectorController;
        $this->renderController      = $renderController;
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
        // remove header that should not be passed to the destination server
        $request = $request->withoutHeader(InputParams::HEADER_TARGET_URL);

        // REQUEST_URI is a non-rewritten URI, original that was passed as a request to the webproxy
        if (($_SERVER['REQUEST_URI'] ?? '') === '/__webproxy/get-ip') {
            return $next($request, $this->selectorController->executeAction($request));
            
        } elseif (($_SERVER['REQUEST_URI'] ?? '') === '/__webproxy/render') {
            return $next($request, $this->renderController->executeAction($request));
        }

        return $next($request, $this->passThroughController->executeAction($request));
    }
}
