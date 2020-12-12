<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ClassWithInjects7
{
    /**
     * @var SomeInterface
     * @DI\Inject("irelevant")
     */
    private $session;

    /**
     * @var SomeProductWithInterface
     * @DI\Inject
     */
    private $product;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ClassWithInjects7
{
    /**
     * @var SomeInterface
     */
    private $session;

    /**
     * @var SomeProductWithInterface
     */
    private $product;
    public function __construct(\Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface $session, \Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface $product)
    {
        $this->session = $session;
        $this->product = $product;
    }
}

?>
