<?php declare(strict_types=1);

namespace Tests\Service;

require_once __DIR__ . '/../../src/Service/AuthChecker.php';

use PHPUnit\Framework\TestCase;
use Wolnosciowiec\WebProxy\Service\AuthChecker;

/**
 * @package Tests
 */
class AuthCheckerTest extends TestCase
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