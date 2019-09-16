<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromVar
{
    /**
     * @Inject
     * @var \Rector\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency
     */
    private $someDependency;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use DI\Annotation\Inject;

class InjectFromVar
{
    /**
     * @var \Rector\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency
     */
    private $someDependency;
    public function __construct(\Rector\Tests\Rector\Property\InjectAnnotationClassRector\Source\ExistingDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

?>
