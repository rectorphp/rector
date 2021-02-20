<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct;

class SkipIfHasChildren
{
    /**
     * @var \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct
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
