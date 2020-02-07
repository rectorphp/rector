<?php

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct;

class ClassWithInjects3
{
    /**
     * @var SomeProduct
     * @inject
     */
    protected $property;
}

?>
-----
<?php

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct;

class ClassWithInjects3
{
    /**
     * @var SomeProduct
     */
    private $property;
    public function __construct(\Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct $property)
    {
        $this->property = $property;
    }
}

?>
