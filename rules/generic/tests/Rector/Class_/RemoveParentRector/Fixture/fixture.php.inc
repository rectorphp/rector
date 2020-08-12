<?php

namespace Rector\Generic\Tests\Rector\Class_\RemoveParentRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;

class SomeClass extends ParentTypeToBeRemoved
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\RemoveParentRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;

class SomeClass
{
}

?>
