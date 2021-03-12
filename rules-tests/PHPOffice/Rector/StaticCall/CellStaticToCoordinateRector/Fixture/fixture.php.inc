<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\CellStaticToCoordinateRector\Fixture;

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

namespace Rector\PHPOffice\Tests\Rector\StaticCall\CellStaticToCoordinateRector\Fixture;

class Fixture
{
    public function run()
    {
        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex();
    }
}

?>
