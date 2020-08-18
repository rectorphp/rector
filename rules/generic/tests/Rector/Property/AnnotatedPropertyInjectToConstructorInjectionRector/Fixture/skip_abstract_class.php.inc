<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

abstract class SkipAbstractClass
{
    /**
     * @var SomeProductWithParent
     * @inject
     */
    protected $property;
}
