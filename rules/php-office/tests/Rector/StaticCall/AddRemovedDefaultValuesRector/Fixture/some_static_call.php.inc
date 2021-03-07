<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\AddRemovedDefaultValuesRector\Fixture;

final class SomeStaticCall
{
    public function run(): void
    {
        \PHPExcel_Cell_DataValidation::setType();
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\AddRemovedDefaultValuesRector\Fixture;

final class SomeStaticCall
{
    public function run(): void
    {
        \PHPExcel_Cell_DataValidation::setType(\PHPExcel_Cell_DataValidation::TYPE_NONE);
    }
}

?>
