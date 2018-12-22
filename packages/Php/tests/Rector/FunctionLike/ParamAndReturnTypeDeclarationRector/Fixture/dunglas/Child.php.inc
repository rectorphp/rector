<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\Dunglas\Child;

class Child extends Foo implements BarInterface
{
    use BazTrait;
    public function test($a)
    {
        parent::test($a);
    }
    /**
     * {@inheritdoc}
     */
    public function baz(array $a, $b)
    {
    }
}

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

trait BazTrait
{
    /**
     * @param int $a
     *
     * @return \DateTime
     */
    protected function inTrait($a)
    {
    }
}

interface BarInterface
{
    /**
     * @param array $a
     * @param int   $b
     *
     * @return float
     */
    public function baz(array $a, $b);
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\Dunglas\Child;

class Child extends Foo implements BarInterface
{
    use BazTrait;
    public function test(float $a)
    {
        parent::test($a);
    }
    /**
     * {@inheritdoc}
     */
    public function baz(array $a, int $b): float
    {
    }
}

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

trait BazTrait
{
    /**
     * @param int $a
     *
     * @return \DateTime
     */
    protected function inTrait(int $a): \DateTime
    {
    }
}

interface BarInterface
{
    /**
     * @param array $a
     * @param int   $b
     *
     * @return float
     */
    public function baz(array $a, int $b): float;
}
?>
