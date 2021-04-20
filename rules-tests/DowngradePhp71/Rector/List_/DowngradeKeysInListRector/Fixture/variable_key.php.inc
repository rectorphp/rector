<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class VariableKey
{
    public function run($id = 'id', $name = 'name'): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        list($id => $id1, $name => $name1) = $data[0];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class VariableKey
{
    public function run($id = 'id', $name = 'name'): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        $id1 = $data[0][$id];
        $name1 = $data[0][$name];
    }
}

?>
