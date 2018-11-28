<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

/**
 * Foo.
 */
class Foo
{
    public function bar($foo)
    {
    }
    /**
     * @param float $a
     */
    public function test($a)
    {
        $closure = function ($a, $c) {
        };
    }
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

/**
 * Foo.
 */
class Foo
{
    public function bar($foo)
    {
    }
    /**
     * @param float $a
     */
    public function test(float $a)
    {
        $closure = function ($a, $c) {
        };
    }
}
?>
