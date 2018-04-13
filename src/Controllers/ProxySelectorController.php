<?php declare(strict_types = 1);

namespace Wolnosciowiec\WebProxy\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Service\Proxy\ProxySelector;
use Zend\Diactoros\Response\JsonResponse;

class ProxySelectorController
{
    /**
     * @var ProxySelector $proxySelector
     */
    private $proxySelector;

    public function __construct(ProxySelector $proxySelector)
    {
        $this->proxySelector = $proxySelector;
    }

    public function executeAction(RequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'address' => $this->proxySelector->getHTTPProxy()
        ]);
    }
}
