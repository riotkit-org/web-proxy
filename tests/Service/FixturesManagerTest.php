<?php declare(strict_types=1);

namespace Tests\Service;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Wolnosciowiec\CustomFixtures\ExampleFixture;
use Wolnosciowiec\WebProxy\Fixtures\NotFoundTo500;
use Wolnosciowiec\WebProxy\Service\FixturesManager;

/**
 * @see FixturesManager
 */
class FixturesManagerTest extends TestCase
{
    /**
     * @see FixturesManager::getFixtures()
     */
    public function testGetFixtures()
    {
        $manager = new FixturesManager('NotFoundTo500', '');
        $this->assertInstanceOf(NotFoundTo500::class, $manager->getFixtures()[0]);
    }

    /**
     * Case: Using a mapping to attach fixtures from external sources
     *
     * @see FixturesManager::getFixtures()
     */
    public function testGetFixturesWithMapping()
    {
        $manager = new FixturesManager(
            'NotFoundTo500,ExampleFixture',
            '{"ExampleFixture": "\\\Wolnosciowiec\\\CustomFixtures\\\ExampleFixture"}'
        );

        $this->assertInstanceOf(NotFoundTo500::class, $manager->getFixtures()[0]);
        $this->assertInstanceOf(ExampleFixture::class, $manager->getFixtures()[1]);
    }

    /**
     * @see FixturesManager::fix()
     */
    public function testFix()
    {
        $manager = new FixturesManager(
            'NotFoundTo500,ExampleFixture',
            '{"ExampleFixture": "\\\Wolnosciowiec\\\CustomFixtures\\\ExampleFixture"}'
        );

        $request = new Request('GET', 'https://static.wolnosciowiec.net/test');
        $response = new Response(404);

        $newResponse = $manager->fix($request, $response);

        $this->assertSame(500, $newResponse->getStatusCode());
        $this->assertArrayHasKey('X-Message', $newResponse->getHeaders());
    }
}
