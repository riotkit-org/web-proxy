<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service;

/**
 * Immutable configuration provider
 */
final class Config
{
    /**
     * @var array $values
     */
    protected $values;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @param string $keyName
     * @return array|string|int|float
     */
    public function get(string $keyName)
    {
        if (!array_key_exists($keyName, $this->values)) {
            throw new \InvalidArgumentException($keyName . ' was not defined in the config.php');
        }

        return $this->values[$keyName];
    }
}
