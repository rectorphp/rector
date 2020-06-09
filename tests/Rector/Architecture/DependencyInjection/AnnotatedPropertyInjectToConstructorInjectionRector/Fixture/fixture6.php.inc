<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

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

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

class ClassWithInjects6
{
    /**
     * @var SomeProductWithParent
     */
    private $property;
    public function __construct(\Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent $property)
    {
        $this->property = $property;
    }
}

?>
