<?php declare(strict_types=1);

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithTrait;

class ClassWithInjects5
{
    /**
     * @var SomeProductWithTrait
     */
    private $property;
    public function __construct(\Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithTrait $property)
    {
        $this->property = $property;
    }
}
