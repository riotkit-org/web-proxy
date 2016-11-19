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
}