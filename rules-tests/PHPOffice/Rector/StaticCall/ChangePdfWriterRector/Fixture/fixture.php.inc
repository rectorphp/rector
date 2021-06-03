<?php

namespace Rector\Tests\PHPOffice\Rector\StaticCall\ChangePdfWriterRector\Fixture;

final class Fixture
{
    public function run(string $format): void
    {
        \PHPExcel_Settings::setPdfRendererName(PHPExcel_Settings::PDF_RENDERER_MPDF);
        \PHPExcel_Settings::setPdfRenderer($somePath);
        $writer = \PHPExcel_IOFactory::createWriter($spreadsheet, 'PDF');
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPOffice\Rector\StaticCall\ChangePdfWriterRector\Fixture;

final class Fixture
{
    public function run(string $format): void
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
    }
}

?>
