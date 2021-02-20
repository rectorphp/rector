<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromVar
{
    /**
     * @Inject
     * @var \Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency
     */
    private $someDependency;
}

?>
-----
<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromVar
{
    public function __construct(private \Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency $someDependency)
    {
    }
}

?>
