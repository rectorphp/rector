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
    /**
     * @var \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency
     */
    protected $someDependency;
    public function __construct(\Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

?>
