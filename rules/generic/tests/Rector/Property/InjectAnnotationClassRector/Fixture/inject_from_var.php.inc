<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromVar
{
    /**
     * @Inject
     * @var \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency
     */
    private $someDependency;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromVar
{
    public function __construct(private \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency $someDependency)
    {
    }
}

?>
