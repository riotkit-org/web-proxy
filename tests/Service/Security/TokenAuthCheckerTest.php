<?php declare(strict_types=1);

namespace Tests\Service\Security;

use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\InputParams;
use Wolnosciowiec\WebProxy\Service\Config;
use Wolnosciowiec\WebProxy\Service\Security\TokenAuthChecker;

/**
 * @see TokenAuthChecker
 */
class TokenAuthCheckerTest extends \Tests\TestCase
{
    public function testInValidToken()
    {
        $authChecker = new TokenAuthChecker(new Config(['apiKey' => 'test-token']));
        $request = new ForwardableRequest($_SERVER, [], null, null, 'php://input', [], [], [InputParams::QUERY_TOKEN => 'this is an invalid key']);

        $this->assertFalse($authChecker->isValid($request));
    }

    public function testValidToken()
    {
        $authChecker = new TokenAuthChecker(new Config(['apiKey' => 'test-token']));
        $request = new ForwardableRequest($_SERVER, [], null, null, 'php://input', [], [], [InputParams::QUERY_TOKEN => 'test-token']);

        $this->assertTrue($authChecker->isValid($request));
    }
}