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
    /**
     * @var \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface
     */
    private $translator;
    public function __construct(\Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}

?>
