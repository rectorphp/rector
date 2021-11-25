<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector\Fixture;

class Foo 
{
    public $value = 'foo';
}

$foo = new Foo();
$closure = function () {
    echo $this->value, PHP_EOL;
};
$args = [];

$closure->call($foo, ...$args);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector\Fixture;

class Foo 
{
    public $value = 'foo';
}

$foo = new Foo();
$closure = function () {
    echo $this->value, PHP_EOL;
};
$args = [];

call_user_func($closure->bindTo($foo, $foo), ...$args);

?>
