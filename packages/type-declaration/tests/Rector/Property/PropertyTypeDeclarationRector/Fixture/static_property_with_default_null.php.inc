<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class StaticPropertyWithDefaultNull
{
    protected static $cacheFile = null;
    protected static $cacheFiles = null;

    /**
     * @return string
     */
    public static function coreCache($file = '')
    {
        return $file;
    }

    /**
     * Register rex_autoload in spl autoloader.
     */
    public static function register()
    {
        self::$cacheFile = self::coreCache();
        self::$cacheFiles[] = self::coreCache();
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class StaticPropertyWithDefaultNull
{
    /**
     * @var string|null
     */
    protected static $cacheFile = null;
    /**
     * @var string[]|null
     */
    protected static $cacheFiles = null;

    /**
     * @return string
     */
    public static function coreCache($file = '')
    {
        return $file;
    }

    /**
     * Register rex_autoload in spl autoloader.
     */
    public static function register()
    {
        self::$cacheFile = self::coreCache();
        self::$cacheFiles[] = self::coreCache();
    }
}

?>
