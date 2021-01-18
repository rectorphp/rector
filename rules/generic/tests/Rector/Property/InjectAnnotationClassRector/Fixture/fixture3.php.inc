<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class ClassWithInjects35
{
    /**
     * @DI\Inject
     */
    private $translator;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class ClassWithInjects35
{
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $translator)
    {
    }
}

?>
