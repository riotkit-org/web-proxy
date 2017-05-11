<?php declare(strict_types=1);

namespace Tests\Fixtures;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Wolnosciowiec\WebProxy\Fixtures\NotFoundTo500;

/**
 * @see NotFoundTo500
 */
class NotFoundTo500Test extends TestCase
{
    /**
     * @see NotFoundTo500::fix()
     */
    public function testFix()
    {
        $request = new Request('GET', 'https://cdn1.wolnosciowiec.net/test');
        $response = new Response(404);

        $newResponse = (new NotFoundTo500())->fixResponse($request, $response);
        $this->assertSame(500, $newResponse->getStatusCode());
    }
}
