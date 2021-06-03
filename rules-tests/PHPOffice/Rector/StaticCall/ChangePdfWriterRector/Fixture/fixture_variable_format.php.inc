<?php

namespace Rector\Tests\PHPOffice\Rector\StaticCall\ChangePdfWriterRector\Fixture;

final class VariableFormatFixture
{
    public function run(string $format): void
    {
        $writer = \PHPExcel_IOFactory::createWriter($spreadsheet, $format);
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPOffice\Rector\StaticCall\ChangePdfWriterRector\Fixture;

final class VariableFormatFixture
{
    public function run(string $format): void
    {
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, $format);
    }
}

?>
