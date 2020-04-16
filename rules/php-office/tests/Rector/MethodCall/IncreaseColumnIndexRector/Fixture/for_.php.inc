<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\IncreaseColumnIndexRector\Fixture;

final class For_
{
    public function run(): void
    {
        $max = 1000;
        $row = 5;
        $worksheet = new \PHPExcel_Worksheet();

        for ($column = 0; $column < $max; $column++) {
            $worksheet->setCellValueByColumnAndRow($column, $row, 'value ' . $column);
        }
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\IncreaseColumnIndexRector\Fixture;

final class For_
{
    public function run(): void
    {
        $max = 1000;
        $row = 5;
        $worksheet = new \PHPExcel_Worksheet();

        for ($column = 1; $column < $max; $column++) {
            $worksheet->setCellValueByColumnAndRow($column, $row, 'value ' . $column);
        }
    }
}

?>
