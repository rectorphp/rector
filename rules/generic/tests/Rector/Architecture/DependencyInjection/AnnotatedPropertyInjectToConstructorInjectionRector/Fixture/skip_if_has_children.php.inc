<?php

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;

class SkipIfHasChildren
{
    /**
     * @var SomeProduct
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
