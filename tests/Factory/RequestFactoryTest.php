<?php declare(strict_types=1);

namespace Tests\Factory;

use Tests\TestCase;
use Wolnosciowiec\WebProxy\Factory\RequestFactory;

/**
 * @see RequestFactory
 * @package Tests\Factory
 */
class RequestFactoryTest extends TestCase
{
    /**
     * @see RequestFactory::create()
     */
    public function testCreate()
    {
        $request = (new RequestFactory())->create('https://wolnosciowiec.net');

        $this->assertSame('wolnosciowiec.net', $request->getUri()->getHost());
        $this->assertNotContains('ww-target-url', $request->getHeaders());
        $this->assertNotContains('ww-token', $request->getHeaders());
    }
}
