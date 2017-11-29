<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Exception\AccessDeniedException;
use Wolnosciowiec\WebProxy\Service\Security\AuthCheckerInterface;

/**
 * Adds authentication by asking multiple checkers
 */
class AuthenticationMiddleware
{
    /**
     * @var AuthCheckerInterface[] $securityCheckers
     */
    private $securityCheckers;

    /**
     * @param AuthCheckerInterface[] $securityCheckers
     */
    public function __construct(array $securityCheckers)
    {
        $this->securityCheckers = $securityCheckers;
    }

    /**
     * @param ForwardableRequest $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @throws \Wolnosciowiec\WebProxy\Exception\AccessDeniedException
     * @return ResponseInterface
     */
    public function __invoke(ForwardableRequest $request, ResponseInterface $response, callable $next)
    {
        foreach ($this->securityCheckers as $checker) {
            if ($checker->canHandle($request) && $checker->isValid($request)) {
                return $next($request, $response);
            }
        }

        throw new AccessDeniedException();
    }
}
