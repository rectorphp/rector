<?php

namespace Rector\Php71\Tests\Rector\List_\ListToArrayDestructRector\Fixture;

class Fixture
{
    public function run()
    {
        list($id1, $name1) = $data;

        foreach ($data as list($id, $name)) {
        }
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\List_\ListToArrayDestructRector\Fixture;

class Fixture
{
    public function run()
    {
        [$id1, $name1] = $data;

        foreach ($data as [$id, $name]) {
        }
    }
}

?>
