<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

final class StaticPropertyWithDefaultNull
{
    private $cacheFile = null;
    private $cacheFiles = null;

    /**
     * @return string
     */
    public function coreCache($file = '')
    {
        return $file;
    }

    /**
     * Register rex_autoload in spl autoloader.
     */
    public function register()
    {
        $this->cacheFile = $this->coreCache();
        $this->cacheFiles[] = $this->coreCache();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

final class StaticPropertyWithDefaultNull
{
    /**
     * @var null|string
     */
    private $cacheFile = null;
    /**
     * @var null|string[]
     */
    private $cacheFiles = null;

    /**
     * @return string
     */
    public function coreCache($file = '')
    {
        return $file;
    }

    /**
     * Register rex_autoload in spl autoloader.
     */
    public function register()
    {
        $this->cacheFile = $this->coreCache();
        $this->cacheFiles[] = $this->coreCache();
    }
}

?>
