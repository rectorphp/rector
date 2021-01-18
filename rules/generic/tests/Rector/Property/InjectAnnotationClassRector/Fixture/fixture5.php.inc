<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

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

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class SomeController2
{
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeEntityManager $entityManager)
    {
    }
}

?>
