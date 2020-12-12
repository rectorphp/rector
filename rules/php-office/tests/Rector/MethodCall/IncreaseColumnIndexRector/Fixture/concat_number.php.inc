<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\IncreaseColumnIndexRector\Fixture;

final class ConcatNumber
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();

        $number = 55;
        $worksheet->setCellValueByColumnAndRow($number + 3, 3, '1150');
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\IncreaseColumnIndexRector\Fixture;

final class ConcatNumber
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();

        $number = 55;
        $worksheet->setCellValueByColumnAndRow($number + 4, 3, '1150');
    }
}

?>
