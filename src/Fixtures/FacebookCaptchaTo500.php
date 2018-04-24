<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Fixtures;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * FacebookCaptchaTo500
 * ====================
 *
 * When it detects a captcha on Facebook page, then
 * a 500 error code is set, so the load balancer could treat the page as invalid
 */
class FacebookCaptchaTo500 implements FixtureInterface
{
    public function fixResponse(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $host = $this->findHost($request);

        // enable only on *facebook.com
        if (strpos($host, 'facebook.com') === false) {
            return $response;
        }

        if (strpos((string) $response->getBody(), 'id="captcha_submit"') !== false) {
            $response = $response->withStatus(500, 'Captcha found');
        }

        return $response;
    }

    protected function findHost(RequestInterface $request)
    {
        if (count($request->getHeader('ww-url')) > 0) {
            return parse_url($request->getHeader('ww-url')[0], PHP_URL_HOST);
        }

        if (count($request->getHeader('Host')) > 0) {
            return $request->getHeader('Host')[0];
        }

        return parse_url($_SERVER['HTTP_WW_TARGET_URL'], PHP_URL_HOST);
    }
}
