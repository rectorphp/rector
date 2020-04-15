<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\CellStaticToCoordinateRector\Fixture;

class SomeClass
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

class SomeClass
{
    public function run()
    {
        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex();
    }
}

?>
