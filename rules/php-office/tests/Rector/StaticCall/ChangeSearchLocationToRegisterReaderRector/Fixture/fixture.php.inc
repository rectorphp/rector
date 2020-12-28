<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\ChangeSearchLocationToRegisterReaderRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        \PHPExcel_IOFactory::addSearchLocation($type, $location, $classname);
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\ChangeSearchLocationToRegisterReaderRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        \PhpOffice\PhpSpreadsheet\IOFactory::registerReader($type, $classname);
    }
}

?>
