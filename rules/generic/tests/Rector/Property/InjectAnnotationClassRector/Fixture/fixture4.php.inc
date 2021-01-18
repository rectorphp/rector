<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class ClassWithPublicInjects
{
    /**
     * @DI\Inject
     */
    public $translator;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use JMS\DiExtraBundle\Annotation as DI;

class ClassWithPublicInjects
{
    public function __construct(public \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $translator)
    {
    }
}

?>
