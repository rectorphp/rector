<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromVar2
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

class InjectFromVar2
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
