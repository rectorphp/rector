<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveParentRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;

class SomeClass extends ParentTypeToBeRemoved
{
}

?>
-----
<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveParentRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;

class SomeClass
{
}

?>
