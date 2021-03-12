<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use JMS\DiExtraBundle\Annotation as DI;

class SomeController2
{
    /**
     * @DI\Inject("Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeEntityManager")
     */
    private $entityManager;
}

?>
-----
<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use JMS\DiExtraBundle\Annotation as DI;

class SomeController2
{
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeEntityManager $entityManager)
    {
    }
}

?>
