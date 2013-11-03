<?php

if(file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else if(file_exists(__DIR__ . '/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
} else {
    file_put_contents('php://stderr', 'Failed to load dependencies. Did you run composer install/update?');
}

use Zend\Console\Console;
use Zend\Console\ColorInterface as Color;

$console = Console::getInstance();

// Obtain console params (inspired by https://github.com/weierophinney/changelog_generator/blob/master/changelog_generator.php)
try {
    $opts = new Zend\Console\Getopt(array(
        'help|h'    => 'Help',
        'project|p-s' => 'Project to scan for Zend Framework 2 components',
        'composer|c-s' => 'Composer file to update', 
    ));
    $opts->parse();
} catch (Zend\Console\Exception\ExceptionInterface $e) {
    file_put_contents('php://stderr', $e->getUsageMessage());
    exit(1);
}

// Print help message if asked or nothing is asked
if(isset($opts->h) || $opts->toArray() == array()) {
    file_put_contents('php://stdout', $opts->getUsageMessage());
    exit(0);
}

$components = array();

// Obtain the list of components
if(isset($opts->p) && is_dir($opts->p)) {
    $directoryIterator = new RecursiveDirectoryIterator($opts->p);
    $iteratorIterator = new RecursiveIteratorIterator($directoryIterator);
    
    // Exclude the paths containing vendor and non-php files
    $regexIterator = new RegexIterator($iteratorIterator, '/^(?!.*vendor.*).*\.php$/i', RecursiveRegexIterator::GET_MATCH);

    foreach($regexIterator as $fileMatches) {
        foreach($fileMatches as $fileMatch) {
            $fileContent = file_get_contents($fileMatch);

            preg_match_all('/Zend\\\\[a-zA-Z0-9]*/', (string)$fileContent, $matches, PREG_PATTERN_ORDER); 

            foreach($matches as $submatches) {
                foreach($submatches as $match) {
                    $zendComponent = strtolower(str_replace('\\', '-', $match));
                    $componentParts = explode('-', $zendComponent);

                    if($componentParts[0] !== '' && $componentParts[1] !== '') {
                        $components[$zendComponent] = true;
                    }
                }
            }

        }
    }
}

if(!empty($components)) {
    $componentsCount = count($components);
    
    // If a composer file is specified replace zendframework/zendframework key with components
    if(isset($opts->c)) {
        $composer = json_decode(file_get_contents($opts->c), true);

        // Remove the zendframework/zendframework
        unset($composer['require']['zendframework/zendframework']);

        // Add all the found components
        foreach(array_keys($components) as $component) {
            $composer['require']['zendframework/' . $component] = '2.*';
        }

        file_put_contents($opts->c, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $console->writeLine($opts->c . ' updated', Color::YELLOW);
    } else {
        // Print the components list to the standard output
        $console->writeLine('Replace "zendframework/zendframework" in your composer.json file with :', Color::YELLOW);
        $count = 0;
        foreach(array_keys($components) as $component) {
            $suffix = '';

            $count++;
            if($count < $componentsCount) {
                $suffix = ',';
            }
            $console->writeLine('"zendframework/' . $component . '": "2.*"' . $suffix);
        }
    }
}