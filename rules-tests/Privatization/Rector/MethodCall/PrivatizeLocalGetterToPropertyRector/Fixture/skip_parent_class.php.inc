<?php

namespace Rector\Tests\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector\Fixture;

class SkipParentClass extends AbstractParentClass
{
    public function run()
    {
        return $this->getSome() + 5;
    }
}

abstract class AbstractParentClass
{
    /**
     * @var int
     */
    private $value = 100;

    public function getSome()
    {
        return $this->value;
    }
}
