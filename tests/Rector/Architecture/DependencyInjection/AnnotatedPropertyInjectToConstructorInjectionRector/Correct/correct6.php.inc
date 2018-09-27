<?php declare(strict_types=1);

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

class ClassWithInjects6
{
    /**
     * @var SomeProductWithParent
     */
    private $property;

    public function __construct(SomeProductWithParent $property)
    {
        $this->property = $property;
    }
}
