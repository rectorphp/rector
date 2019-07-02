<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

class InjectFromVar
{
    /**
     * @Inject
     * @var \Fully\Qualified\ClassName\To\Dependency
     */
    private $someDependency;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

class InjectFromVar
{
    /**
     * @var \Fully\Qualified\ClassName\To\Dependency
     */
    private $someDependency;
    public function __construct(\Fully\Qualified\ClassName\To\Dependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

?>
