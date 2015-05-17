<?php

if (file_exists(__DIR__ . '/../autoload.php')) {
    require_once __DIR__ . '/../autoload.php';
} elseif(file_exists(__DIR__ . '/../../../autoload.php')) {
    require_once __DIR__ . '/../../../autoload.php';
} elseif(file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif(file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
} else {
    file_put_contents('php://stderr', 'Failed to load dependencies. Did you run composer install/update?');
    exit(1);
}

use Zend\Console\Console;
use Zend\Console\ColorInterface as Color;
use Rb\Generator\Zf2ComponentsList as Zf2ComponentsListGenerator;

// Obtain console params (inspired by https://github.com/weierophinney/changelog_generator/blob/master/changelog_generator.php)
try {
    $opts = new Zend\Console\Getopt(array(
        'help|h'       => 'Help',
        'project|p-s'  => 'Project to scan for Zend Framework 2 components',
        'composer|c-s' => 'Composer file to update',
        'version|v-s'  => 'Zend Framework 2 version to use'
    ));
    $opts->parse();
} catch (Zend\Console\Exception\ExceptionInterface $e) {
    file_put_contents('php://stderr', $e->getUsageMessage());
    exit(1);
}

// Print help message if asked or nothing is asked
if (isset($opts->h) || $opts->toArray() == array()) {
    file_put_contents('php://stdout', $opts->getUsageMessage());
    exit(0);
}

// Create options
$options = array();

if (isset($opts->v)) {
    $options['version'] = $opts->v;
}

$zf2ComponentsListGenerator = new Zf2ComponentsListGenerator($options);

$generator  = $zf2ComponentsListGenerator->scan($opts->p);
$components = $generator->getComponents();

$console = Console::getInstance();

if (!empty($components)) {
    if (isset($opts->c)) {
        if (!is_file($opts->c)) {
            $console->writeLine($opts->c . ' file does not exist!');
        } else {
            $generator->toFile($opts->c);
            $console->writeLine($opts->c . ' updated', Color::YELLOW);
        }
    } else {
        $generator->toConsole();
    }
} else {
    $console->writeLine('No Zend Framework 2 components found!', Color::GRAY);
}
