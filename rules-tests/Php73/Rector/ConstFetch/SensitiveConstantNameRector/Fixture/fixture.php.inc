<?php

namespace Rector\Tests\Php73\Rector\ConstFetch\SensitiveConstantNameRector\Fixture;

function sensitiveConstantName()
{
    define('FOO', 42, true);

    FOO;
    var_dump(FOO);

    foo;
    var_dump(foo);
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\ConstFetch\SensitiveConstantNameRector\Fixture;

function sensitiveConstantName()
{
    define('FOO', 42, true);

    FOO;
    var_dump(FOO);

    \FOO;
    var_dump(\FOO);
}

?>
