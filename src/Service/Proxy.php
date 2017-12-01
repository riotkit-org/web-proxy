<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Proxy\Exception\UnexpectedValueException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Relay\RelayBuilder;

class Proxy extends \Proxy\Proxy
{
    public function to($target)
    {
        if (is_null($this->request))
        {
            throw new UnexpectedValueException('Missing request instance.');
        }

        $target = new Uri($target);

        // Overwrite target scheme and host.
        $uri = $this->request->getUri()
                             ->withScheme($target->getScheme())
                             ->withHost($target->getHost());

        // Check for custom port.
        if ($port = $target->getPort()) {
            $uri = $uri->withPort($port);
        }

        // Check for subdirectory.
        if ($path = $target->getPath()) {
            // this line was fixed - it causes an issue in Wolnościowiec WebProxy, all links are getting "/" at the end
            // which makes the proxy useless
            $uri = $uri->withPath(rtrim($path, '/'));
        }

        $request = $this->request->withUri($uri);

        $stack = $this->filters;

        $stack[] = function (RequestInterface $request, ResponseInterface $response, callable $next)
        {
            return $next($request, $this->adapter->send($request));
        };

        $relay = (new RelayBuilder())->newInstance($stack);

        return $relay($request, new Response());
    }
}