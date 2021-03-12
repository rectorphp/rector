<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\ChangeIOFactoryArgumentRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $writer = \PHPExcel_IOFactory::createWriter('CSV');
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\ChangeIOFactoryArgumentRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $writer = \PHPExcel_IOFactory::createWriter('Csv');
    }
}

?>
