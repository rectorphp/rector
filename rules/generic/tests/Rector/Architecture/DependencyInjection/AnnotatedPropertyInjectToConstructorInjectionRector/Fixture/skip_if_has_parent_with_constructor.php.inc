<?php

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;

class SkipIfHasParentWithConstructor extends ParentWithConstructor
{
    /**
     * @var SomeProduct
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
