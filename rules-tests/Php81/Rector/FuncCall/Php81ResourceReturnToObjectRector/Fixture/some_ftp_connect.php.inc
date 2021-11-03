<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeFtpConnect
{
    public function run(string $server)
    {
        $f = ftp_connect($server);
        is_resource($f);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeFtpConnect
{
    public function run(string $server)
    {
        $f = ftp_connect($server);
        $f instanceof \FTP\Connection;
    }
}

?>
