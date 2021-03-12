<?php

namespace Rector\Tests\PHPOffice\Rector\MethodCall\GetDefaultStyleToGetParentRector\Fixture;

class Fixture
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

namespace Rector\Tests\PHPOffice\Rector\MethodCall\GetDefaultStyleToGetParentRector\Fixture;

class Fixture
{
    public function run()
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getParent()->getDefaultStyle();
    }
}

?>
