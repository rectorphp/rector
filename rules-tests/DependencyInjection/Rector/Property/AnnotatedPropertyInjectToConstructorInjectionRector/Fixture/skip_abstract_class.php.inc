<?php

namespace Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

abstract class SkipAbstractClass
{
    /**
     * @var SomeProductWithParent
     * @inject
     */
    protected $property;
}
