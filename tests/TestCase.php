<?php declare(strict_types=1);

namespace Tests;

use DI\Container;

/**
 * @package Tests
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Container
     */
    public function getContainer()
    {
        return $GLOBALS['container'];
    }
}
