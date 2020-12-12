<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalGetConditionRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->getCondition();
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalGetConditionRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->getConditions()[0] ?? '';
    }
}

?>
