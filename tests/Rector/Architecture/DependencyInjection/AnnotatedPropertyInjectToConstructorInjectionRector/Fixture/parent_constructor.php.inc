<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;
use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ClassWithInjects7 extends ClassWithInjects7Parent
{
    /**
     * @var SomeProduct
     * @inject
     */
    public $someProduct;
}

class ClassWithInjects7Parent
{
    /**
     * @var SomeProductWithInterface
     */
    private $someProductWith;

    public function __construct(SomeProductWithInterface $someProductWith)
    {
        $this->someProductWith = $someProductWith;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;
use Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ClassWithInjects7 extends ClassWithInjects7Parent
{
    /**
     * @var SomeProduct
     */
    private $someProduct;
    public function __construct(SomeProductWithInterface $someProductWith, \Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct $someProduct)
    {
        $this->someProduct = $someProduct;
        parent::__construct($someProductWith);
    }
}

class ClassWithInjects7Parent
{
    /**
     * @var SomeProductWithInterface
     */
    private $someProductWith;

    public function __construct(SomeProductWithInterface $someProductWith)
    {
        $this->someProductWith = $someProductWith;
    }
}

?>
