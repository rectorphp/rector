<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Source\HasAInterface;

trait A
{
    public function getA()
    {
        return "A";
    }
}

trait B
{
    public function getB()
    {
        if ($this instanceof HasAInterface) {
            return $this->getA();
        }
    }
}

class C implements HasAInterface
{
    use A;
    use B;
}

class D
{
    use B;
}

echo (new C())->getB();
echo (new D())->getB();
