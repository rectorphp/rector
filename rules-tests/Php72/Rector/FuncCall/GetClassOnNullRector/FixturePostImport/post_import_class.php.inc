<?php

namespace Rector\Tests\Php72\Rector\FuncCall\GetClassOnNullRector\FixturePostImport;

final class PostImportClass
{
    /**
     * @var \Rector\Tests\Php72\Rector\FuncCall\GetClassOnNullRector\Source\SomeSuperLongClass|null
     */
    private $cache;

    public function getItem()
    {
        $message = get_class($this->cache);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\FuncCall\GetClassOnNullRector\FixturePostImport;

use Rector\Tests\Php72\Rector\FuncCall\GetClassOnNullRector\Source\SomeSuperLongClass;
final class PostImportClass
{
    /**
     * @var SomeSuperLongClass|null
     */
    private $cache;

    public function getItem()
    {
        $message = $this->cache !== null ? get_class($this->cache) : self::class;
    }
}

?>
