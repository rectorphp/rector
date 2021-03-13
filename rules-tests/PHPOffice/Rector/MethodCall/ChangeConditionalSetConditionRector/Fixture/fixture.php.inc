<?php

namespace Rector\Tests\PHPOffice\Rector\MethodCall\ChangeConditionalSetConditionRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->setCondition(1);
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPOffice\Rector\MethodCall\ChangeConditionalSetConditionRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->setConditions((array) 1);
    }
}

?>
