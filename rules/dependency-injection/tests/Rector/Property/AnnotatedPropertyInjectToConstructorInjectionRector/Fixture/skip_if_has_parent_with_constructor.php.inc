<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct;

class SkipIfHasParentWithConstructor extends ParentWithConstructor
{
    /**
     * @var \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct
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
