<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

interface HasA
{
    public function getA();
}

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
        if ($this instanceof HasA) {
            return $this->getA();
        }
    }
}

class C implements HasA
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
