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

    public function run2($singleData): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        $singleData2 = $this->run();
        foreach ($data as list("id" => $id1, "name" => $name1)) {
            echo $singleData;
        }
        echo $singleData2;
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
        foreach ($data as $singleData2) {
            $id1 = $singleData2["id"];
            $name1 = $singleData2["name"];
            echo $singleData;
        }
    }

    public function run2($singleData): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        $singleData2 = $this->run();
        foreach ($data as $singleData3) {
            $id1 = $singleData3["id"];
            $name1 = $singleData3["name"];
            echo $singleData;
        }
        echo $singleData2;
    }
}

?>
