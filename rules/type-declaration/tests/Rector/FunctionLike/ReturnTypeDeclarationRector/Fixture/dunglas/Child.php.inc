<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas\Child;

class Child extends Foo implements BarInterface
{
    use BazTrait;
    public function test($a, $b)
    {
        return parent::test($a, $b);
    }
    /**
     * {@inheritdoc}
     */
    public function baz(array $a, $b, $c)
    {
        return $c;
    }
}

class Foo
{
    public function bar($foo)
    {
        return $foo;
    }
    /**
     * @param float $a
     */
    public function test($a, $b)
    {
        $closure = function ($a, $c) {
        };

        return $b;
    }
}

trait BazTrait
{
    /**
     * @param int $a
     *
     * @return \DateTime
     */
    protected function inTrait($a, $b)
    {
        return $b;
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
    public function baz(array $a, $b, $c);
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas\Child;

class Child extends Foo implements BarInterface
{
    use BazTrait;
    public function test($a, $b)
    {
        return parent::test($a, $b);
    }
    /**
     * {@inheritdoc}
     */
    public function baz(array $a, $b, $c): float
    {
        return $c;
    }
}

class Foo
{
    public function bar($foo)
    {
        return $foo;
    }
    /**
     * @param float $a
     */
    public function test($a, $b)
    {
        $closure = function ($a, $c) {
        };

        return $b;
    }
}

trait BazTrait
{
    /**
     * @param int $a
     *
     * @return \DateTime
     */
    protected function inTrait($a, $b): \DateTime
    {
        return $b;
    }
}

interface BarInterface
{
    /**
     * @param array $a
     * @param int   $b
     */
    public function baz(array $a, $b, $c): float;
}
?>
