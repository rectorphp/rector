<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct;

class SkipIfHasChildren
{
    /**
     * @var \Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct
     * @inject
     */
    public $someProduct;
}

class ChildOfOneClass extends SkipIfHasChildren
{
    public function __construct()
    {
    }
}
