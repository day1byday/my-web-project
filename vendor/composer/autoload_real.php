<?php

class ComposerAutoloaderInit
{
    private static $loader;

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        // Composer "files" autoload - 手动加载 helper 文件
        $filesToLoad = [
            __DIR__ . '/../topthink/think-helper/src/helper.php',
        ];
        foreach ($filesToLoad as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
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
