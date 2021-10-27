<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeSocket
{
    public function run()
    {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        is_resource($socket);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeSocket
{
    public function run()
    {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        $socket instanceof \Socket;
    }
}

?>
