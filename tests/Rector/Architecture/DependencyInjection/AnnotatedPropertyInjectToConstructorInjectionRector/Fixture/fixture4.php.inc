<?php

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ClassWithInjects4
{
    /**
     * @var SomeProductWithInterface
     * @inject
     */
    protected $property;
}

?>
-----
<?php

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ClassWithInjects4
{
    /**
     * @var SomeProductWithInterface
     */
    private $property;
    public function __construct(\Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface $property)
    {
        $this->property = $property;
    }
}

?>
