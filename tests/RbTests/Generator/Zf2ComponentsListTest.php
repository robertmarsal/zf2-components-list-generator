<?php

/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2013 Servei de Recursos Educatius (http://www.sre.urv.cat)
 */

namespace RbTests\Generator;

use Rb\Generator\Zf2ComponentsList as Zf2ComponentsListGenerator;
use PHPUnit_Framework_TestCase;

class Zf2ComponentsListTest extends PHPUnit_Framework_TestCase
{
    const TEST_PROJECT = '_fixtures/TestProject/';

    public function testScan()
    {
        $zf2ComponentsListGenerator = new Zf2ComponentsListGenerator();

        $this->assertEquals(
            $zf2ComponentsListGenerator->scan(__DIR__ . '/' . self::TEST_PROJECT)
                                       ->getComponents(),
            array(
                "zend-authentication" => true,
                "zend-barcode"        => true,
                "zend-cache"          => true,
                "zend-captcha"        => true,
                "zend-code"           => true,
                "zend-console"        => true,
                "zend-stdlib"         => true,
                "zend-log"            => true,
                "zend-config"         => true,
            )
        );
    }

    public function testGetConsoleReturnsAnInstanceOfConsole()
    {
        $zf2ComponentsListGenerator = new Zf2ComponentsListGenerator();

        $this->assertInstanceOf(
            '\Zend\Console\Adapter\AdapterInterface',
            $zf2ComponentsListGenerator->getConsole()
        );
    }
}