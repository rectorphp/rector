<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector\Fixture;

class Foo 
{
    public $value = 'foo';
}

$closure = function () {
    echo $this->value, PHP_EOL;
};
$args = [];

$closure->call(new Foo(), ...$args);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector\Fixture;

class Foo 
{
    public $value = 'foo';
}

$closure = function () {
    echo $this->value, PHP_EOL;
};
$args = [];

call_user_func($closure->bindTo(...array_fill(0, 2, new Foo())), ...$args);

?>
