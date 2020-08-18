<?php

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;

class SkipIfHasChildren
{
    /**
     * @var \Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct
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
