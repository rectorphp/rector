<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeMsgGetQueue
{
    public function run()
    {
        $seg = msg_get_queue($MSGKey) ;
        is_resource($seg);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeMsgGetQueue
{
    public function run()
    {
        $seg = msg_get_queue($MSGKey) ;
        $seg instanceof \SysvMessageQueue;
    }
}

?>
