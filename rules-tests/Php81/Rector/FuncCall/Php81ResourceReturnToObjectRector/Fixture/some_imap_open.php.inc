<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeImapOpen
{
    public function run()
    {
        $mbox = imap_open("{localhost:143}INBOX", "user_id", "password");
        is_resource($mbox);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeImapOpen
{
    public function run()
    {
        $mbox = imap_open("{localhost:143}INBOX", "user_id", "password");
        $mbox instanceof \IMAP\Connection;
    }
}

?>
