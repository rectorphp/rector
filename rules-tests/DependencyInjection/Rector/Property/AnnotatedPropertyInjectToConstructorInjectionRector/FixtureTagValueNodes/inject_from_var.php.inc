<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use DI\Annotation\Inject;

class InjectFromVar
{
    /**
     * @Inject
     * @var \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\ExistingDependency
     */
    private $someDependency;
}

?>
-----
<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use DI\Annotation\Inject;

class InjectFromVar
{
    public function __construct(private \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\ExistingDependency $someDependency)
    {
    }
}

?>
