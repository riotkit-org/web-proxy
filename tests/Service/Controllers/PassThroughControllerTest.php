<?php declare(strict_types=1);

namespace Tests\Controllers;

use Tests\TestCase;
use Wolnosciowiec\WebProxy\Controllers\PassThroughController;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Exception\HttpException;
use Wolnosciowiec\WebProxy\InputParams;

/**
 * @see PassThroughController
 */
class PassThroughControllerTest extends TestCase
{
    /**
     * Test valid url
     */
    public function testValidUrl()
    {
        $request = new ForwardableRequest($_SERVER, [], null, null, 'php://input', [], [], [
            InputParams::QUERY_TOKEN => 'your-api-key-here',
            InputParams::QUERY_TARGET_URL => 'http://wolnywroclaw.pl',
        ]);

        $controller = $this->getContainer()->get(PassThroughController::class);
        $response = (string)$controller->executeAction($request)->getBody();

        $this->assertContains('Federacja Anarchistyczna', $response);
    }

    /**
     * Test HTTP 404 response
     */
    public function testInvalidUrl()
    {
        $request = new ForwardableRequest($_SERVER, [], null, null, 'php://input', [], [], [
            InputParams::QUERY_TOKEN => 'your-api-key-here',
            InputParams::QUERY_TARGET_URL => 'https://github.com/this_should_not_exist_fegreiuhwif',
        ]);

        $controller = $this->getContainer()->get(PassThroughController::class);
        $response = $controller->executeAction($request);

        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * Test of catching connection errors
     * ----------------------------------
     *   Expecting: cURL error 6: Could not resolve host: this-domain-should-not-exists (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)
     */
    public function testHttpErrorUrl()
    {
        $request = new ForwardableRequest($_SERVER, [], null, null, 'php://input', [], [], [
            InputParams::QUERY_TOKEN => 'your-api-key-here',
            InputParams::QUERY_TARGET_URL => 'http://1.2.3.4',
        ]);

        $controller = $this->getContainer()->get(PassThroughController::class);
        $response = $controller->executeAction($request);

        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * Check if there are required headers in the request
     */
    public function testRequestValidation()
    {
        $this->expectException(HttpException::class);

        $request = new ForwardableRequest($_SERVER, [], null, null, 'php://input', [], [], [
            InputParams::QUERY_TOKEN => 'your-api-key-here',
        ]);

        $controller = $this->getContainer()->get(PassThroughController::class);
        $controller->executeAction($request);
    }
}
