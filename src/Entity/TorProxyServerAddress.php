<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Entity;

/**
 * Represents a proxy details with additional logic behind
 */
class TorProxyServerAddress extends ProxyServerAddress
{
    /**
     * @var int $torManagementPort
     */
    private $torManagementPort;

    /**
     * @var int $authPassword
     */
    private $authPassword;

    public function __construct(int $torManagementPort = 9051, string $authPassword = '')
    {
        $this->torManagementPort = $torManagementPort;
        $this->authPassword      = $authPassword;
    }

    /**
     * When the request is going to be executed then
     * the exit node needs to be switched
     */
    public function prepare()
    {
        $fp = fsockopen($this->getAddress(), $this->torManagementPort, $errNo, $errStr, 30);

        if ($this->authPassword) {
            fwrite($fp, 'AUTHENTICATE "' . $this->authPassword . "\"\r\n");
        }

        fwrite($fp, "SIGNAL NEWNYM\r\n");
        fclose($fp);
    }

    /**
     * TOR network handles this inside of the network itself
     *
     * @return bool
     */
    public function requiresVerification(): bool
    {
        return false;
    }
}
