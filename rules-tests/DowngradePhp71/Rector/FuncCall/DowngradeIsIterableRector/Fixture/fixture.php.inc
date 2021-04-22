<?php

namespace Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector\Fixture;

class Fixture
{
    public function run($obj)
    {
        is_iterable($obj);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector\Fixture;

class Fixture
{
    public function run($obj)
    {
        is_array($obj) || $obj instanceof \Traversable;
    }
}

?>
