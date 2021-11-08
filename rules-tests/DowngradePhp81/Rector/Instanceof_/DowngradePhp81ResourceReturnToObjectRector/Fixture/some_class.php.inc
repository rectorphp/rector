<?php

namespace Rector\Tests\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector\Fixture;

class SomeClass
{
    public function run($obj)
    {
        $obj instanceof \finfo;
        $obj instanceof \FTP\Connection;
        $obj instanceof \IMAP\Connection;
        $obj instanceof \PSpell\Config;
        $obj instanceof \PSpell\Dictionary;
        $obj instanceof \LDAP\Result;
        $obj instanceof \LDAP\ResultEntry;
        $obj instanceof \PgSql\Result;
        $obj instanceof \PgSql\Lob;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector\Fixture;

class SomeClass
{
    public function run($obj)
    {
        is_resource($obj) || $obj instanceof \finfo;
        is_resource($obj) || $obj instanceof \FTP\Connection;
        is_resource($obj) || $obj instanceof \IMAP\Connection;
        is_resource($obj) || $obj instanceof \PSpell\Config;
        is_resource($obj) || $obj instanceof \PSpell\Dictionary;
        is_resource($obj) || $obj instanceof \LDAP\Result;
        is_resource($obj) || $obj instanceof \LDAP\ResultEntry;
        is_resource($obj) || $obj instanceof \PgSql\Result;
        is_resource($obj) || $obj instanceof \PgSql\Lob;
    }
}

?>
