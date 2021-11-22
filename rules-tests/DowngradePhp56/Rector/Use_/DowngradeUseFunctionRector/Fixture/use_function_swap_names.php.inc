<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

use function Foo\foo as bar;
use function Foo\bar as foo;
use function Foo\baz as qux;

function baz()
{
}

$var = foo() + bar() + baz() + qux();

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

function baz()
{
}

$var = \Foo\bar() + \Foo\foo() + baz() + \Foo\baz();

?>
