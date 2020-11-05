<?php

class ComposerAutoloaderInitRandomHash
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\\Autoload\\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }
}

?>
-----
<?php

class ComposerAutoloaderInitRandomHash
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Prefix_\\Composer\\Autoload\\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }
}

?>
