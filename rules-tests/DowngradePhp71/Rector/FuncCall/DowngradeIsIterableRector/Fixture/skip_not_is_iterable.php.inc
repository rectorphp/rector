<?php

namespace Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector\Fixture;

class SkipNotIsIterable
{
    public function run($obj)
    {
        return is_object($obj);
    }
}

?>
