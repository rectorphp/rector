<?php

namespace Rector\Php\Tests\Rector\ConstFetch\BarewordStringRector\Fixture;

function run_define()
{
    define(NAME, 'Tom');
    define('AGE', 1000);
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ConstFetch\BarewordStringRector\Fixture;

function run_define()
{
    define(NAME, 'Tom');
    define('AGE', 1000);
}

?>
