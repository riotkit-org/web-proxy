<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Entity;

/**
 * Represents a proxy details
 * --------------------------
 *
 * @package Wolnosciowiec\WebProxy\Entity
 */
class ProxyServerAddress
{
    /**
     * @var string $address
     */
    private $address;

    /**
     * @var int $port
     */
    private $port = 80;

    /**
     * @var string http|https
     */
    private $schema;

    /**
     * @param string $address
     * @return ProxyServerAddress
     */
    public function setAddress(string $address): ProxyServerAddress
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @param int $port
     * @return ProxyServerAddress
     */
    public function setPort(int $port): ProxyServerAddress
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param string $schema
     * @return ProxyServerAddress
     */
    public function setSchema(string $schema): ProxyServerAddress
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->getSchema() === 'https';
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getFormatted()
    {
        return $this->getSchema() . '://' . $this->getAddress() . ':' . $this->getPort();
    }
}
