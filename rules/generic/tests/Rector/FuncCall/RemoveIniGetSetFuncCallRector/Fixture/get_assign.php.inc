<?php

namespace Rector\Generic\Tests\Rector\FuncCall\RemoveIniGetSetFuncCallRector\Fixture;

class GetAssign
{
    public function run()
    {
        $getAssign = ini_get('safe_mode');

        $notGetAssign = ! ini_get('safe_mode');
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\FuncCall\RemoveIniGetSetFuncCallRector\Fixture;

class GetAssign
{
    public function run()
    {
        // @fixme
        $getAssign = ini_get('safe_mode');

        // @fixme
        $notGetAssign = ! ini_get('safe_mode');
    }
}

?>
