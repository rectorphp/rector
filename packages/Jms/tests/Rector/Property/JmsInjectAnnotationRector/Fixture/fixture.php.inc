<?php

namespace Rector\Jms\Tests\Rector\Property\JmsInjectAnnotationRector\Wrong;

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

    /**
     * @DI\Inject("%kernel.cache_dir%")
     */
    private $cacheDir;
}

?>
-----
<?php

namespace Rector\Jms\Tests\Rector\Property\JmsInjectAnnotationRector\Wrong;

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

    /**
     * @DI\Inject("%kernel.cache_dir%")
     */
    private $cacheDir;
    public function __construct(\Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeEntityManager $entityManager, \Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }
}

?>
