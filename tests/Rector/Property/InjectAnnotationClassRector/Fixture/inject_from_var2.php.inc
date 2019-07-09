<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

class InjectFromProtectedVar
{
    /**
     * @Inject
     * @var \Fully\Qualified\ClassName\To\Dependency
     */
    protected $someDependency;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

class InjectFromProtectedVar
{
    /**
     * @var \Fully\Qualified\ClassName\To\Dependency
     */
    protected $someDependency;
    public function __construct(\Fully\Qualified\ClassName\To\Dependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

?>
