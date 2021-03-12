<?php

namespace Rector\Tests\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector\Fixture;

class Skip
{
    /**
     * @var Collection|Foo[]
     */
    private $foo;

    /**
     * @var ArrayObject|int[]
     */
    public $property;

    /**
     * @var SplObjectStorage|int[]
     */
    public $anotherProperty;

    /**
     * @var \ArrayObject|int[]
     */
    public $propertyWithPreslash;

    /**
     * @var \SplObjectStorage|int[]
     */
    public $anotherPropertyWithPreslash;
}
