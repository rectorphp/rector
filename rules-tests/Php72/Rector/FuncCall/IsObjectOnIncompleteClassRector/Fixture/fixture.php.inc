<?php

namespace Rector\Tests\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector\Fixture;

use __PHP_Incomplete_Class;

final class SomeIncompleteClass
{
    public function run()
    {
        $incompleteObject = new __PHP_Incomplete_Class;
        $isObject = is_object($incompleteObject);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector\Fixture;

use __PHP_Incomplete_Class;

final class SomeIncompleteClass
{
    public function run()
    {
        $incompleteObject = new __PHP_Incomplete_Class;
        $isObject = !is_object($incompleteObject);
    }
}

?>
