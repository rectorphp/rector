<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    /**
     * @DI\Inject("entity.manager")
     */
    private $entityManager;

    /**
     * @DI\Inject("translator", strict = false, required = false)
     */
    private $translator;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    /**
     * @var \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeEntityManager
     */
    private $entityManager;

    /**
     * @var \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface
     */
    private $translator;
    public function __construct(\Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeEntityManager $entityManager, \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }
}

?>
