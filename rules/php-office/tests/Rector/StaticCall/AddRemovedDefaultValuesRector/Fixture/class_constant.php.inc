<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\AddRemovedDefaultValuesRector\Fixture;

final class ClassConstant
{
    public function run(): void
    {
        $dataValidation = new \PHPExcel_Cell_DataValidation;
        $dataValidation->setType();
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\AddRemovedDefaultValuesRector\Fixture;

final class ClassConstant
{
    public function run(): void
    {
        $dataValidation = new \PHPExcel_Cell_DataValidation;
        $dataValidation->setType(\PHPExcel_Cell_DataValidation::TYPE_NONE);
    }
}

?>
