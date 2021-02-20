<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use DI\Annotation\Inject;

class InjectFromProtectedVar
{
    /**
     * @Inject
     * @var \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\ExistingDependency
     */
    protected $someDependency;
}

?>
-----
<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use DI\Annotation\Inject;

class InjectFromProtectedVar
{
    public function __construct(protected \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\ExistingDependency $someDependency)
    {
    }
}

?>
