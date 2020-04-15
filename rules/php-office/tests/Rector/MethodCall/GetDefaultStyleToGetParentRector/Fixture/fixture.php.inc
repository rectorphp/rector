<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\GetDefaultStyleToGetParentRector\Fixture;

class SomeClass
{
    public function run()
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getDefaultStyle();
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\MethodCall\GetDefaultStyleToGetParentRector\Fixture;

class SomeClass
{
    public function run()
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getParent()->getDefaultStyle();
    }
}

?>
