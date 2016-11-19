<?php

namespace Tests;

require_once __DIR__ . '/../src/Service/AuthChecker.php';

use Wolnosciowiec\WebProxy\Service\AuthChecker;

/**
 * @package Tests
 */
class AuthCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testInValidToken()
    {
        putenv('WW_TOKEN=test-token');

        $authChecker = new AuthChecker();
        $this->assertFalse($authChecker->validate('this is an invalid key'));
    }

    public function testValidToken()
    {
        putenv('WW_TOKEN=test-token');

        $authChecker = new AuthChecker();
        $this->assertTrue($authChecker->validate('test-token'));
    }
}