<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\IncreaseColumnIndexRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(0, 3, '1150');
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\IncreaseColumnIndexRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(1, 3, '1150');
    }
}

?>
