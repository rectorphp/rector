<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeDuplicateStyleArrayToApplyFromArrayRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->duplicateStyleArray($styles, $range, $advanced);
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeDuplicateStyleArrayToApplyFromArrayRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getStyle($range)->applyFromArray($styles, $advanced);
    }
}

?>
