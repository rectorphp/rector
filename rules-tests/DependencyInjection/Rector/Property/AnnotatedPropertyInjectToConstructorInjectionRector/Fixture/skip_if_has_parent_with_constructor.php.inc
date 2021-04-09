<?php

namespace Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

final class SkipIfHasParentWithConstructor extends ParentWithConstructor
{
    /**
     * @var \Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct
     * @inject
     */
    public $someProduct;
}

class ParentWithConstructor
{
    public function __construct($value = 100)
    {
    }
}
