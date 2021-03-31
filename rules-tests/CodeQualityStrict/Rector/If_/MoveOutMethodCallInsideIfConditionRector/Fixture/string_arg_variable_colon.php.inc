<?php

namespace Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use DateTime;

final class StringArgVariableColon
{
    public function run()
    {
        $dt = new DateTime();
        if ($dt->format('H:i:s') == '00:00:00') {
            echo "test".PHP_EOL;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use DateTime;

final class StringArgVariableColon
{
    public function run()
    {
        $dt = new DateTime();
        $dtFormat = $dt->format('H:i:s');
        if ($dtFormat == '00:00:00') {
            echo "test".PHP_EOL;
        }
    }
}

?>
