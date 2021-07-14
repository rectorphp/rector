<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class AssignToArrayDimFetchVariable
{
    public function run()
    {
        $someString = '';
        $someString[] = 1;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class AssignToArrayDimFetchVariable
{
    public function run()
    {
        $someString = [];
        $someString[] = 1;
    }
}

?>
