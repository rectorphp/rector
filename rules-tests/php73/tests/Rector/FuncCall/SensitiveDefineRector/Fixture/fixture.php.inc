<?php

namespace Rector\Php73\Tests\Rector\FuncCall\SensitiveDefineRector\Fixture;

function sensitiveDefine()
{
    define('FOO', 42, true);
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\SensitiveDefineRector\Fixture;

function sensitiveDefine()
{
    define('FOO', 42);
}

?>
