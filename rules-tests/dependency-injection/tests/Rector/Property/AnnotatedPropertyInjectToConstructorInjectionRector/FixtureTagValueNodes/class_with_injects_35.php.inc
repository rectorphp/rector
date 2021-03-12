<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

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

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use JMS\DiExtraBundle\Annotation as DI;

class ClassWithInjects35
{
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\AbstractToConstructorInjectionRectorSource\Contract\SomeTranslatorInterface $translator)
    {
    }
}

?>
