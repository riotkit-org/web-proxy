<?php declare(strict_types=1);

namespace Tests\Fixtures;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Wolnosciowiec\WebProxy\Fixtures\FacebookCaptchaTo500;

/**
 * @see FacebookCaptchaTo500
 */
class FacebookCaptchaTo500Test extends TestCase
{
    /**
     * @see FacebookCaptchaTo500::fix()
     */
    public function testFix()
    {
        $request = new Request('GET', 'https://facebook.com/');
        $response = new Response(200, [], '<div id="captcha_submit"></div>');

        $newResponse = (new FacebookCaptchaTo500())->fixResponse($request, $response);
        $this->assertSame(500, $newResponse->getStatusCode());
    }

    /**
     * @see FacebookCaptchaTo500::fix()
     */
    public function testNotMatchingDomain()
    {
        $request = new Request('GET', 'https://not-a-facebook-domain.org/');
        $response = new Response(200, [], '<div id="captcha_submit"></div>');

        $newResponse = (new FacebookCaptchaTo500())->fixResponse($request, $response);
        $this->assertSame(200, $newResponse->getStatusCode());
    }
}
