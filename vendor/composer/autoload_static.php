<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit94dede6b12d64c3638c6b37d69a78f0a
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Google\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Google\\' => 
        array (
            0 => __DIR__ . '/..' . '/asimlqt/php-google-spreadsheet-client/src/Google',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit94dede6b12d64c3638c6b37d69a78f0a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit94dede6b12d64c3638c6b37d69a78f0a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
