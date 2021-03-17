<?php

namespace Rector\Tests\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector\Fixture;

class InForeach
{
    public function __construct(array $data)
    {
        foreach ($data as [$id1, $name1]) {

        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector\Fixture;

class InForeach
{
    public function __construct(array $data)
    {
        foreach ($data as list($id1, $name1)) {

        }
    }
}

?>
