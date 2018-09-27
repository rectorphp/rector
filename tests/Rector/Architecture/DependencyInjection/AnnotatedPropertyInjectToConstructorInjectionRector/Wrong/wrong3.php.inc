<?php declare(strict_types=1);

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct;

class ClassWithInjects3
{
    /**
     * @var SomeProduct
     * @inject
     */
    protected $property;
}
