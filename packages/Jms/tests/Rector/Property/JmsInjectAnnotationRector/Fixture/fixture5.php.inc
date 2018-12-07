<?php

namespace Rector\Jms\Tests\Rector\Property\JmsInjectAnnotationRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class SomeController2
{
    /**
     * @DI\Inject("Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeEntityManager")
     */
    private $entityManager;
}

?>
-----
<?php

namespace Rector\Jms\Tests\Rector\Property\JmsInjectAnnotationRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class SomeController2
{
    /**
     * @var \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeEntityManager
     */
    private $entityManager;
    public function __construct(\Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeEntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}

?>
