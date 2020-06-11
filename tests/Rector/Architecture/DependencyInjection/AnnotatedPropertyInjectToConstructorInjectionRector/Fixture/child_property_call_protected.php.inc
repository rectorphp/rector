<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ChildPropertyCallProtected extends SomeParentWithInject
{
    public function run()
    {
        return $this->someProductWith;
    }
}

class SomeParentWithInject
{
    /**
     * @inject
     * @var SomeProductWithInterface
     */
    public $someProductWith;
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ChildPropertyCallProtected extends SomeParentWithInject
{
    public function run()
    {
        return $this->someProductWith;
    }
}

class SomeParentWithInject
{
    /**
     * @var SomeProductWithInterface
     */
    protected $someProductWith;
    public function __construct(\Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface $someProductWith)
    {
        $this->someProductWith = $someProductWith;
    }
}

?>
