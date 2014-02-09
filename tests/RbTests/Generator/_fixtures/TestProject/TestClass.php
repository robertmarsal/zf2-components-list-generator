<?php

/**
 * Test components are detected from use statements.
 */
use Zend\Authentication\Result;
use Zend\Barcode\Barcode;
use Zend\Cache;
use Zend\Captcha\Image;
use Zend\Code;
use Zend\Console;
use Zend\Stdlib;

class TestClass
{
    /**
     * Test components are detected when used in methods
     */
    public function testMethod()
    {
        $logger = new Zend\Log\Logger();
        $config = new Zend\Config\Config();
    }
}