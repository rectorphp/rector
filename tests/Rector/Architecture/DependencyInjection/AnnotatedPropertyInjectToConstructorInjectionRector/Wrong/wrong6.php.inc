<?php

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

class ClassWithInjects6
{
    /**
     * @var SomeProductWithParent
     * @inject
     */
    protected $property;
}

?>
-----
<?php

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

class ClassWithInjects6
{
    /**
     * @var SomeProductWithParent
     */
    private $property;
    public function __construct(\Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent $property)
    {
        $this->property = $property;
    }
}

?>
