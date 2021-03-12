<?php

namespace Rector\Tests\PHPOffice\Rector\StaticCall\CellStaticToCoordinateRector\Fixture;

class Fixture
{
    public function run()
    {
        \PHPExcel_Cell::stringFromColumnIndex();
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPOffice\Rector\StaticCall\CellStaticToCoordinateRector\Fixture;

class Fixture
{
    public function run()
    {
        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex();
    }
}

?>
