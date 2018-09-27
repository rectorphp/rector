<?php declare(strict_types=1);

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

class ClassWithInjects6
{
    /**
     * @var SomeProductWithParent
     * @inject
     */
    protected $property;
}
