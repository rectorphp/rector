<?php declare(strict_types=1);

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ClassWithInjects4
{
    /**
     * @var SomeProductWithInterface
     * @inject
     */
    protected $property;
}
