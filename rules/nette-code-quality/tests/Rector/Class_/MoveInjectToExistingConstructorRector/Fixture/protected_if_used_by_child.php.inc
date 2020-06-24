<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Fixture;

use Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Source\SomeDependency;

class ProtectedIfUsedByChild
{
    /**
     * @var SomeDependency
     * @inject
     */
    public $someDependency;

    public function __construct()
    {
    }
}

final class ChildUser extends ProtectedIfUsedByChild
{
    public function run()
    {
        return $this->someDependency;
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Fixture;

use Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Source\SomeDependency;

class ProtectedIfUsedByChild
{
    /**
     * @var SomeDependency
     */
    protected $someDependency;

    public function __construct(\Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Source\SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

final class ChildUser extends ProtectedIfUsedByChild
{
    public function run()
    {
        return $this->someDependency;
    }
}

?>
