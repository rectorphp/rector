<?php

namespace Rector\Core\Tests\Issues\Issue3514\Fixture;

class Fixture3514SetShartedStyle
{
    public function run()
    {
        $bookmark = 'book';

        $workbook = new \PHPExcel();

        $sheet = $workbook->setActiveSheetIndex(0);
        $sheet->setTitle($bookmark);
        $sheet->setSharedStyle(new StyleWithoutBorder('9999FF', 10, true, 'HORIZONTAL_CENTER',0,'Arial','VERTICAL_CENTER',false,false,'FFFFFF'), "A1:N1");
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue3514\Fixture;

class Fixture3514SetShartedStyle
{
    public function run()
    {
        $bookmark = 'book';

        $workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $sheet = $workbook->setActiveSheetIndex(0);
        $sheet->setTitle($bookmark, true);
        $sheet->duplicateStyle(new StyleWithoutBorder('9999FF', 10, true, 'HORIZONTAL_CENTER',0,'Arial','VERTICAL_CENTER',false,false,'FFFFFF'), "A1:N1");
    }
}

?>
