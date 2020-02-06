<?php

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithTrait;

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

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithTrait;

class ClassWithInjects5
{
    /**
     * @var SomeProductWithTrait
     */
    private $property;
    public function __construct(\Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithTrait $property)
    {
        $this->property = $property;
    }
}

?>
