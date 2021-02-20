<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromProtectedVar
{
    /**
     * @Inject
     * @var \Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency
     */
    protected $someDependency;
}

?>
-----
<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromProtectedVar
{
    public function __construct(protected \Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency $someDependency)
    {
    }
}

?>
