<?php

namespace Rector\Generic\Tests\Rector\Class_\AddPropertyByParentRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\AddPropertyByParentRector\Source\SomeParentClassToAddDependencyBy;

final class SomeClass extends SomeParentClassToAddDependencyBy
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\AddPropertyByParentRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\AddPropertyByParentRector\Source\SomeParentClassToAddDependencyBy;

final class SomeClass extends SomeParentClassToAddDependencyBy
{
    private \SomeDependency $someDependency;
    public function __construct(\SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

?>
