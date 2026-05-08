<?php

class ComposerAutoloaderInit
{
    private static $loader;

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(function ($class) {
            $map = require __DIR__ . '/autoload_classmap.php';
            if (isset($map[$class])) {
                require __DIR__ . '/../' . $map[$class];
            }
        });

        self::$loader = true;
        return self::$loader;
    }
}
