<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

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

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

class ClassWithInjects7
{
    public function __construct(private \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface $session, private \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface $product)
    {
    }
}

?>
