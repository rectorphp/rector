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
    public function __construct(private \Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface $session, private \Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface $product)
    {
    }
}

?>
