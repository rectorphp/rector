<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

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

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class StaticPropertyWithDefaultNull
{
    private ?string $cacheFile = null;
    /**
     * @var string[]|null
     */
    private ?array $cacheFiles = null;

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
