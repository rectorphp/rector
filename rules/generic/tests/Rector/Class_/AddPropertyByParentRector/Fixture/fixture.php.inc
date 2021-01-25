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
    public function __construct(private \SomeDependency $someDependency)
    {
    }
}

?>
