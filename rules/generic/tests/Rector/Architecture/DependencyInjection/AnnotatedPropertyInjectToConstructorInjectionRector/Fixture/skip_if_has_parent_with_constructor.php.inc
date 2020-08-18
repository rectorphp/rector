<?php

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;

class SkipIfHasParentWithConstructor extends ParentWithConstructor
{
    /**
     * @var \Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct
     * @inject
     */
    public $someProduct;
}

class ParentWithConstructor
{
    public function __construct()
    {
    }
}
