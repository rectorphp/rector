<?php

namespace Rector\Tests\Php73\Rector\FuncCall\SensitiveDefineRector\Fixture;

function sensitiveDefine()
{
    define('FOO', 42, true);
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\FuncCall\SensitiveDefineRector\Fixture;

function sensitiveDefine()
{
    define('FOO', 42);
}

?>
