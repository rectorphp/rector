<?php

namespace Rector\DowngradePhp71\Tests\Rector\Array_\SymmetricArrayDestructuringToListRector\Fixture;

class Fixture
{
    public function __construct(array $data)
    {
        [$id1, $name1] = $data;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\Array_\SymmetricArrayDestructuringToListRector\Fixture;

class Fixture
{
    public function __construct(array $data)
    {
        list($id1, $name1) = $data;
    }
}

?>
