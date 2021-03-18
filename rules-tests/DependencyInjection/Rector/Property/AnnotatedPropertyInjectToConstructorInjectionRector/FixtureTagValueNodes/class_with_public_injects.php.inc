<?php

namespace Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

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

namespace Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use JMS\DiExtraBundle\Annotation as DI;

class ClassWithPublicInjects
{
    public function __construct(public \Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeTranslatorInterface $translator)
    {
    }
}

?>
