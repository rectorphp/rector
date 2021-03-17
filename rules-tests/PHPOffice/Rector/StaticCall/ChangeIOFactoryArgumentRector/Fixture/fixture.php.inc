<?php

namespace Rector\Tests\PHPOffice\Rector\StaticCall\ChangeIOFactoryArgumentRector\Fixture;

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

namespace Rector\Tests\PHPOffice\Rector\StaticCall\ChangeIOFactoryArgumentRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $writer = \PHPExcel_IOFactory::createWriter('Csv');
    }
}

?>
