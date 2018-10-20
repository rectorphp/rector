<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Wrong;

use Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;
use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

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
