<?php

namespace Rector\Tests\PHPOffice\Rector\MethodCall\RemoveSetTempDirOnExcelWriterRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $writer = new \PHPExcel_Writer_Excel5;
        $writer->setTempDir();
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPOffice\Rector\MethodCall\RemoveSetTempDirOnExcelWriterRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $writer = new \PHPExcel_Writer_Excel5;
    }
}

?>
