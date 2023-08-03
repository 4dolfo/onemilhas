<?php

class register {

    static $map = array(
        'MilesBench\\Model\\' => array('\Model'),
        'MilesBench\\' => array(''),
        'MilesBench\\Model\\EntitiesBase\\' => array('\Model\EntitiesBase'),
        'MilesBench\\Controller\\' => array('\Model\EntitiesBase'),
        'MilesBench\\Controller\\App\\' => array('\Model\EntitiesBase')
    );

    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        $appDir = __DIR__.'\MilesBench';

        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('register', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array('register', 'loadClassLoader'));

        foreach (self::$map as $namespace => $path) {
            $loader->set($namespace, $appDir.$path[0]);
        }

        $loader->register(true);

        return $loader;
    }
}

