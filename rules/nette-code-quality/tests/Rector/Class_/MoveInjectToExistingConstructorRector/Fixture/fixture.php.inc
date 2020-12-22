<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Fixture;

final class Fixture
{
    /**
     * @var SomeDependency
     * @inject
     */
    public $someDependency;

    /**
     * @var OtherDependency
     */
    private $otherDependency;

    public function __construct(OtherDependency $otherDependency)
    {
        $this->otherDependency = $otherDependency;
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Fixture;

final class Fixture
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    /**
     * @var OtherDependency
     */
    private $otherDependency;

    public function __construct(OtherDependency $otherDependency, \Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Fixture\SomeDependency $someDependency)
    {
        $this->otherDependency = $otherDependency;
        $this->someDependency = $someDependency;
    }
}

?>
