<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class AsForeachValueUsed
{
    public function run($singleData): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        foreach ($data as list("id" => $id, "name" => $name)) {
            echo $singleData;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\Fixture;

class AsForeachValueUsed
{
    public function run($singleData): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        foreach ($data as $singleData1) {
            $id = $singleData1["id"];
            $name = $singleData1["name"];
            echo $singleData;
        }
    }
}

?>
