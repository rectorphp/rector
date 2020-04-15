<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalReturnedCellRector\Fixture;

final class SetExplicit
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $cell = $worksheet->setCellValueExplicitByColumnAndRow('A1', 'value', 'bla', 'bla', true);
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalReturnedCellRector\Fixture;

final class SetExplicit
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $cell = $worksheet->getCellByColumnAndRow('A1', 'value')->setValueExplicit('bla', 'bla');
    }
}

?>
