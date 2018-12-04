<?php

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithTrait;

class ClassWithInjects5
{
    /**
     * @var SomeProductWithTrait
     * @inject
     */
    protected $property;
}

?>
-----
<?php

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

?>
