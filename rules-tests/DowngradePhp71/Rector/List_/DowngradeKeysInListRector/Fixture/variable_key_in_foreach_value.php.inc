<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class VariableKeyInForeachValue
{
    public function run($id = 'id', $name = 'name'): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        foreach ($data as list($id => $id1, $name => $name1)) {
            echo 'statement';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class VariableKeyInForeachValue
{
    public function run($id = 'id', $name = 'name'): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        foreach ($data as $singleData) {
            $id1 = $singleData[$id];
            $name1 = $singleData[$name];
            echo 'statement';
        }
    }
}

?>
