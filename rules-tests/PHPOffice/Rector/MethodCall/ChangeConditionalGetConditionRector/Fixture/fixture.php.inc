<?php

namespace Rector\Tests\PHPOffice\Rector\MethodCall\ChangeConditionalGetConditionRector\Fixture;

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

namespace Rector\Tests\PHPOffice\Rector\MethodCall\ChangeConditionalGetConditionRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->getConditions()[0] ?? '';
    }
}

?>
