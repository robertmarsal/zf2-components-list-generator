<?php

namespace Rb\Generator;

use Zend\Console\Console;
use Zend\Console\ColorInterface as Color;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Zf2ComponentsList
{
    const CURRENT_VERSION = '2.3.*';

    /**
     * Console instance for printing output.
     *
     * @var \Zend\Console\Console
     */
    protected $console;

    /**
     * Contains the scanned components.
     *
     * @var array
     */
    protected $components = array();

    /**
     * Current Zend Framework 2 version to use.
     *
     * @var string
     */
    protected $version;

    /**
     * Creates a new instance
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (isset($options['version'])) {
            $this->version = $options['version'];
        } else {
            $this->version = self::CURRENT_VERSION;
        }
    }

    /**
     * Scans a directory (recursive) and obtains a list of Zend\* components.
     *
     * @param string $project
     * @return \Rb\Generator\Zf2ComponentsList
     */
    public function scan($project)
    {
        //@TODO: allow scan of individual files
        // First check if target is a directory
        if (!is_dir($project)) {
            $this->getConsole()->writeLine($project . ' is not a directory!');
            return $this;
        }

        $iteratorIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($project));

        //@TODO: make ignored paths a param
        // Exclude the paths containing vendor and non-php files
        $regexIterator = new \RegexIterator($iteratorIterator, '/^(?!.*vendor.*).*\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($regexIterator as $fileMatches) {
            foreach ($fileMatches as $fileMatch) {

                $fileContent = file_get_contents($fileMatch);

                preg_match_all('/Zend\\\\[a-zA-Z0-9]*/', (string) $fileContent, $matches, PREG_PATTERN_ORDER);

                foreach ($matches as $submatches) {
                    foreach ($submatches as $match) {
                        $zendComponent = strtolower(str_replace('\\', '-', $match));
                        $componentParts = explode('-', $zendComponent);

                        if ($componentParts[0] !== '' && $componentParts[1] !== '') {
                            $this->components[$zendComponent] = true;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Updates the composer.json file with the new components list.
     *
     * @param string $file
     */
    public function toFile($file)
    {
        $composer = json_decode(file_get_contents($file), true);

        // Remove the zendframework/zendframework
        unset($composer['require']['zendframework/zendframework']);

        // Add all the found components
        foreach (array_keys($this->components) as $component) {
            $composer['require']['zendframework/' . $component] = $this->version;
        }

        file_put_contents($file, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Sends the components list to the console.
     */
    public function toConsole()
    {
        $this->getConsole()->writeLine(
                'Replace "zendframework/zendframework" in your composer.json file with :', Color::YELLOW
        );
        $componentsCount = count($this->components);
        $count = 0;

        foreach (array_keys($this->components) as $component) {
            $suffix = '';

            $count++;
            if ($count < $componentsCount) {
                $suffix = ',';
            }
            $this->getConsole()->writeLine('"zendframework/' . $component . '": "' . $this->version . '"' . $suffix);
        }
    }

    /**
     * Returns the current list of found components.
     *
     * @return array
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * Returns an instance of the console, if one does not already exist.
     *
     * @return \Zend\Console\Console
     */
    public function getConsole()
    {
        if (!$this->console) {
            $this->console = Console::getInstance();
        }

        return $this->console;
    }
}
