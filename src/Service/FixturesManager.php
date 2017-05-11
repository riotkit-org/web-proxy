<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wolnosciowiec\WebProxy\Exception\InvalidConfigurationException;
use Wolnosciowiec\WebProxy\Fixtures\FixtureInterface;

/**
 * FixturesManager
 * ===============
 *
 * Loads fixtures from this repository and registered
 * from other included libraries/repositories
 */
class FixturesManager
{
    /**
     * @var FixtureInterface[] $fixtures
     */
    protected $fixtures = [];

    /**
     * Example $mapping value: {"NotFoundTo500": "\\Some\\Name\\Space\\In\\A\\Library\\NotFoundTo500"}
     *
     * @param string $fixturesNames Comma separated values
     * @param string $mapping       JSON
     */
    public function __construct(string $fixturesNames, string $mapping)
    {
        $this->load(
            explode(',', $fixturesNames),
            json_decode($mapping, true) ?? []
        );
    }

    /**
     * Load all fixtures, internal and external
     *
     * @param array $names
     * @param array $mapping
     *
     * @throws InvalidConfigurationException
     */
    protected function load(array $names, array $mapping)
    {
        $names = array_filter($names);

        foreach ($names as $name) {
            $className = $this->getFullClassName($name, $mapping);
            $this->fixtures[] = new $className();
        }
    }

    /**
     * Get class name for a fixture
     * looking at first in internal fixtures, then into mapped fixtures
     * from libraries/external files accessible via composer's autoloader
     *
     * @param string $name
     * @param array $mapping
     *
     * @throws InvalidConfigurationException
     * @return string
     */
    protected function getFullClassName(string $name, array $mapping)
    {
        $className = 'Wolnosciowiec\\WebProxy\\Fixtures\\' . $name;

        if (class_exists($className)) {
            return $className;
        }

        if (!isset($mapping[$name])) {
            throw new InvalidConfigurationException(
                '"' . $name . '" fixture not found in standard namespace,
                you probably should define it in mapping. Use "fixtures_mapping" configuration variable
                or WW_FIXTURES_MAPPING environment variable.'
            );
        }

        return $mapping[$name];
    }

    /**
     * @return FixtureInterface[]
     */
    public function getFixtures()
    {
        return $this->fixtures;
    }

    /**
     * Apply all enabled fixtures
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function fix(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        foreach ($this->fixtures as $fixture) {
            $response = $fixture->fixResponse($request, $response);
        }

        return $response;
    }
}
