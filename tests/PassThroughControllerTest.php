<?php

namespace Tests;

require_once __DIR__ . '/../src/Controllers/PassThroughController.php';

use Wolnosciowiec\WebProxy\Controllers\PassThroughController;

/**
 * @package Tests
 */
class PassThroughControllerTest extends TestCase
{
    /**
     * Test valid url
     */
    public function testValidUrl()
    {
        $_SERVER['HTTP_WW_TARGET_URL'] = 'https://github.com/Wolnosciowiec';

        $controller = $this->getContainer()->get(PassThroughController::class);
        $response = (string)$controller->executeAction()->getBody();

        $this->assertContains('Wolnościowiec.net', $response);
    }

    /**
     * Test HTTP 404 response
     */
    public function testInvalidUrl()
    {
        $_SERVER['HTTP_WW_TARGET_URL'] = 'https://github.com/this_should_not_exist_fegreiuhwif';

        /** @var PassThroughController $controller */
        $controller = $this->getContainer()->get(PassThroughController::class);
        $response = $controller->executeAction();

        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * Test of catching connection errors
     * ----------------------------------
     *   Expecting: cURL error 6: Could not resolve host: this-domain-should-not-exists (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)
     */
    public function testHttpErrorUrl()
    {
        $_SERVER['HTTP_WW_TARGET_URL'] = 'http://1.2.3.4';

        /** @var PassThroughController $controller */
        $controller = $this->getContainer()->get(PassThroughController::class);
        $response = $controller->executeAction();

        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * Check if there are required headers in the request
     */
    public function testRequestValidation()
    {
        unset($_SERVER['HTTP_WW_TARGET_URL']);

        /** @var PassThroughController $controller */
        $controller = $this->getContainer()->get(PassThroughController::class);
        $response = json_decode((string)$controller->executeAction()->getBody(), true);

        $this->assertFalse($response['success']);
        $this->assertContains('Request URL not specified.', $response['message']);
    }
}
