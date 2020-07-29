<?php

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

abstract class SkipAbstractClass
{
    /**
     * @var SomeProductWithParent
     * @inject
     */
    protected $property;
}
