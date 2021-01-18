<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

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

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeEntityManager $entityManager, private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $translator)
    {
    }
}

?>
