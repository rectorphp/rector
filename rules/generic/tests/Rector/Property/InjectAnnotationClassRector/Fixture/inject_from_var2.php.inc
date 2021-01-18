<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromProtectedVar
{
    /**
     * @Inject
     * @var \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency
     */
    protected $someDependency;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromProtectedVar
{
    public function __construct(protected \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency $someDependency)
    {
    }
}

?>
