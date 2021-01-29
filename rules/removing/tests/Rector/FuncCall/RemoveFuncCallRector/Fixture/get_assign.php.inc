<?php

namespace Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallRector\Fixture;

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

namespace Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallRector\Fixture;

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
