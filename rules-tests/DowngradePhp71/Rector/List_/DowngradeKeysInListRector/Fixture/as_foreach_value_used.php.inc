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
        foreach ($data as list("id" => $id1, "name" => $name1)) {
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
            $id1 = $singleData1["id"];
            $name1 = $singleData1["name"];
            echo $singleData;
        }
    }
}

?>
