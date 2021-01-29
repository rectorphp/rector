<?php

namespace Rector\Removing\Tests\Rector\Class_\RemoveParentRector\Fixture;

use Rector\Removing\Tests\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;

class SomeClass extends ParentTypeToBeRemoved
{
}

?>
-----
<?php

namespace Rector\Removing\Tests\Rector\Class_\RemoveParentRector\Fixture;

use Rector\Removing\Tests\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;

class SomeClass
{
}

?>
