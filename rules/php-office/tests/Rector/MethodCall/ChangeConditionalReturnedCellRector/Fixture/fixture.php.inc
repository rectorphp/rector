<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalReturnedCellRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $cell = $worksheet->setCellValue('A1', 'value', true);
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalReturnedCellRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $cell = $worksheet->getCell('A1')->setValue('value');
    }
}

?>
