<?php

namespace GpxTrackerExamples;

/**
 *
 * Autoloader for GpxTrackerExamples classes
 *
 * @package GpxReader
 * @copyright  Copyright (c) 2015 Mark Baker (https://github.com/MarkBaker/GpxReader)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class Autoloader
{
    /**
     * @var    string    $namespace    Namespace assigned to be handled by this autoloader instance
     */
	private $namespace;

    /**
     * Register the Autoloader with SPL
     *
     * @param    string    $namespace    The namespace to be handled by this autoloader instance
	 * @return   boolean   true on success; false on failure
     */
    public function register($namespace) {
		$this->namespace = $namespace;

        if (function_exists('__autoload')) {
            //    Register any existing autoloader function with SPL, so we don't get any clashes
            spl_autoload_register('__autoload');
        }
        //    Register ourselves with SPL
        return spl_autoload_register(array($this, 'load'));
    }


    /**
     * Autoload a class identified by name
     *
     * @param    string    $className    Name of the object to load
	 * @return   boolean|null    false if the class hasn't been autoloaded, null if it has
     */
    public function load($className) {
        if ((class_exists($className, false))) {
            // Either already loaded, or not a GpxReader class request
            return false;
        }

        $classFilePath = str_replace("\\",DIRECTORY_SEPARATOR, __DIR__ . DIRECTORY_SEPARATOR .
                          'src' . DIRECTORY_SEPARATOR .
                          $className) .
                          '.php';
        if ((file_exists($classFilePath) === false) || (is_readable($classFilePath) === false)) {
            // Can't load
            return false;
        }
        require($classFilePath);
    }
}
