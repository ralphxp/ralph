<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitfabdb31bfdcf9ea7e7dbeae1f74ac822
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitfabdb31bfdcf9ea7e7dbeae1f74ac822', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitfabdb31bfdcf9ea7e7dbeae1f74ac822', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitfabdb31bfdcf9ea7e7dbeae1f74ac822::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
