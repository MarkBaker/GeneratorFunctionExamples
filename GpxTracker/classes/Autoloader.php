<?php

namespace GpxReader;

/**
 *
 * Autoloader for GpxReader classes
 *
 * @package GpxReader
 * @copyright  Copyright (c) 2015 Mark Baker (https://github.com/MarkBaker/GpxReader)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class Autoloader
{
    /**
     * Register the Autoloader with SPL
     *
     */
    public static function register() {
        if (function_exists('__autoload')) {
            //    Register any existing autoloader function with SPL, so we don't get any clashes
            spl_autoload_register('__autoload');
        }
        //    Register ourselves with SPL
        return spl_autoload_register(array('GpxReader\Autoloader', 'load'));
    }


    /**
     * Autoload a class identified by name
     *
     * @param    string    $className    Name of the object to load
     */
    public static function load($className) {
        if ((class_exists($className, false)) || (strpos($className, 'GpxReader\\') !== 0)) {
            // Either already loaded, or not a GpxReader class request
            return false;
        }

        $classFilePath = __DIR__ . DIRECTORY_SEPARATOR .
                          'src' . DIRECTORY_SEPARATOR .
                          str_replace('GpxReader\\', '', $className) .
                          '.php';

        if ((file_exists($classFilePath) === false) || (is_readable($classFilePath) === false)) {
            // Can't load
            return false;
        }
        require($classFilePath);
    }
}