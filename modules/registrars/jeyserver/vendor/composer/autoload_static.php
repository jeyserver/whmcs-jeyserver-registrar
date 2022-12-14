<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit160c47f07f86d244d322daf3f3c09691
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WHMCS\\Module\\Registrar\\JeyServer\\' => 33,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WHMCS\\Module\\Registrar\\JeyServer\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit160c47f07f86d244d322daf3f3c09691::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit160c47f07f86d244d322daf3f3c09691::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit160c47f07f86d244d322daf3f3c09691::$classMap;

        }, null, ClassLoader::class);
    }
}
