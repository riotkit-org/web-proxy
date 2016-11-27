<?php

namespace Tests;

require_once __DIR__ . '/../src/Controllers/PassThroughController.php';

use Wolnosciowiec\WebProxy\Controllers\PassThroughController;

/**
 * @package Tests
 */
class PassThroughControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test valid url
     */
    public function testValidUrl()
    {
        $_SERVER['HTTP_WW_TARGET_URL'] = 'https://github.com/Wolnosciowiec';

        $controller = new PassThroughController();
        $response = $controller->executeAction();

        $this->assertContains('Wolnościowiec.net', $response);
    }

    /**
     * Test HTTP 404 response
     */
    public function testInvalidUrl()
    {
        $_SERVER['HTTP_WW_TARGET_URL'] = 'https://github.com/this_should_not_exist_fegreiuhwif';

        $controller = new PassThroughController();
        $response = json_decode($controller->executeAction(), true);

        $this->assertFalse($response['success']);
    }

    /**
     * Test of catching connection errors
     * ----------------------------------
     *   Expecting: cURL error 6: Could not resolve host: this-domain-should-not-exists (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)
     */
    public function testHttpErrorUrl()
    {
        $_SERVER['HTTP_WW_TARGET_URL'] = 'http://this-domain-should-not-exists';

        $controller = new PassThroughController();
        $response = json_decode($controller->executeAction(), true);

        $this->assertFalse($response['success']);
        $this->assertSame('Connection error', $response['message']);
        $this->assertContains('Could not resolve host', $response['details']);
    }

    /**
     * Check if there are required headers in the request
     */
    public function testRequestValidation()
    {
        unset($_SERVER['HTTP_WW_TARGET_URL']);

        $controller = new PassThroughController();
        $response = json_decode($controller->executeAction(), true);

        $this->assertFalse($response['success']);
        $this->assertContains('Request URL not specified.', $response['message']);
    }
}