<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb9a6eaf26ced231ae6937641fefd7e31
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LemonInk\\' => 9,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LemonInk\\' => 
        array (
            0 => __DIR__ . '/..' . '/lemonink/lemonink-php/lib',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $prefixesPsr0 = array (
        'L' => 
        array (
            'LemonInk\\' => 
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb9a6eaf26ced231ae6937641fefd7e31::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb9a6eaf26ced231ae6937641fefd7e31::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitb9a6eaf26ced231ae6937641fefd7e31::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitb9a6eaf26ced231ae6937641fefd7e31::$classMap;

        }, null, ClassLoader::class);
    }
}
