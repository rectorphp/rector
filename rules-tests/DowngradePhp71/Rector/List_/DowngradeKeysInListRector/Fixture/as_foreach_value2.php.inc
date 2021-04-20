<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class AsForeachValue2
{
    public function run(): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        foreach ($data as list("id" => $id, "name" => $name)) {
            echo 'statement';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class AsForeachValue2
{
    public function run(): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        foreach ($data as $singleData) {
            $id = $singleData["id"];
            $name = $singleData["name"];
            echo 'statement';
        }
    }
}

?>
