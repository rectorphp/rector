<?php

namespace Rector\DowngradePhp71\Tests\Rector\Array_\SymmetricArrayDestructuringToListRector\Fixture;

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

namespace Rector\DowngradePhp71\Tests\Rector\Array_\SymmetricArrayDestructuringToListRector\Fixture;

class InForeach
{
    public function __construct(array $data)
    {
        foreach ($data as list($id1, $name1)) {

        }
    }
}

?>
