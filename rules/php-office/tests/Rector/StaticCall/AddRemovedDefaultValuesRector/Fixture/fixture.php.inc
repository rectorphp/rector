<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\AddRemovedDefaultValuesRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $logger = new \PHPExcel_CalcEngine_Logger;
        $logger->setWriteDebugLog();
    }
}

?>
-----
<?php

namespace Rector\PHPOffice\Tests\Rector\StaticCall\AddRemovedDefaultValuesRector\Fixture;

final class Fixture
{
    public function run(): void
    {
        $logger = new \PHPExcel_CalcEngine_Logger;
        $logger->setWriteDebugLog(false);
    }
}

?>
