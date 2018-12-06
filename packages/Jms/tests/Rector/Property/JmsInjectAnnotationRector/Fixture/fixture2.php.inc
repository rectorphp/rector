<?php

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

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

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface;

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
    public function __construct(\Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface $session, \Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProductWithInterface $product)
    {
        $this->session = $session;
        $this->product = $product;
    }
}

?>
