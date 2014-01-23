<?php

namespace Rb\Generator;

use Zend\Console\Console;
use Zend\Console\ColorInterface as Color;

class Zf2ComponentsList
{
	protected $console;

	protected $components = array();

	public function __construct()
	{
		$this->console = Console::getInstance();
	}

	public function scan($project)
	{
		//@TODO: allow scan of individual files
		// First check if target is a directory
		if(!is_dir($project)) {
			$this->console->writeLine($project . ' is not a directory!');
			return $this;
		}

	    $directoryIterator = new \RecursiveDirectoryIterator($project);
    	$iteratorIterator = new \RecursiveIteratorIterator($directoryIterator);

    	//@TODO: make ignored paths a param
	    // Exclude the paths containing vendor and non-php files
    	$regexIterator = new \RegexIterator($iteratorIterator, '/^(?!.*vendor.*).*\.php$/i', \RecursiveRegexIterator::GET_MATCH);

	    foreach($regexIterator as $fileMatches) {
	        foreach($fileMatches as $fileMatch) {
	            $fileContent = file_get_contents($fileMatch);

	            preg_match_all('/Zend\\\\[a-zA-Z0-9]*/', (string)$fileContent, $matches, PREG_PATTERN_ORDER);

	            foreach($matches as $submatches) {
	                foreach($submatches as $match) {
	                    $zendComponent = strtolower(str_replace('\\', '-', $match));
	                    $componentParts = explode('-', $zendComponent);

	                    if($componentParts[0] !== '' && $componentParts[1] !== '') {
	                        $this->components[$zendComponent] = true;
	                    }
	                }
	            }

	        }
    	}

    	return $this;
	}

	public function toFile($file)
	{
		if(empty($this->components)) {
			return false;
		}

        if(!is_file($file)) {
            $this->console->writeLine($file . ' file does not exist!');
            return false;
        }

        $composer = json_decode(file_get_contents($file), true);

        // Remove the zendframework/zendframework
        unset($composer['require']['zendframework/zendframework']);

        // Add all the found components
        foreach(array_keys($this->components) as $component) {
            $composer['require']['zendframework/' . $component] = '2.*';
        }

        file_put_contents($file, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->console->writeLine($file . ' updated', Color::YELLOW);
	}

	public function toConsole()
	{
		if(empty($this->components)) {
			return false;
		}

        $this->console->writeLine('Replace "zendframework/zendframework" in your composer.json file with :', Color::YELLOW);
        $componentsCount = count($this->components);
        $count = 0;
        foreach(array_keys($this->components) as $component) {
            $suffix = '';

            $count++;
            if($count < $componentsCount) {
                $suffix = ',';
            }
            $this->console->writeLine('"zendframework/' . $component . '": "2.*"' . $suffix);
        }
	}
}