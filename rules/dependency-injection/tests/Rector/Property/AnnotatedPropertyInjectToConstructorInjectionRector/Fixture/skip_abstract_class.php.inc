<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

abstract class SkipAbstractClass
{
    /**
     * @var SomeProductWithParent
     * @inject
     */
    protected $property;
}
