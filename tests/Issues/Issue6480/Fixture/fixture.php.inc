<?php

namespace Rector\Core\Tests\Issues\Issue6480\Fixture;

class Fixture
{
    function test(Foo $foo, mixed $bar): void {
        if ($foo instanceof Foo) {
            var_dump(!empty($bar) ? $bar : null);
        }
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6480\Fixture;

class Fixture
{
    function test(Foo $foo, mixed $bar): void {
        var_dump(empty($bar) ? null : $bar);
    }
}

?>
