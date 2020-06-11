<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

class ClassWithParentWithConstructor extends SomeClassWithParent
{
    /**
     * @var SomeProductWithParent
     * @inject
     */
    protected $property;
}

class SomeClassWithParent
{
    private $value;

    public function __construct()
    {
        $this->value = '123';
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent;

class ClassWithParentWithConstructor extends SomeClassWithParent
{
    /**
     * @var SomeProductWithParent
     */
    private $property;
    public function __construct(\Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithParent $property)
    {
        $this->property = $property;
        parent::__construct();
    }
}

class SomeClassWithParent
{
    private $value;

    public function __construct()
    {
        $this->value = '123';
    }
}

?>
