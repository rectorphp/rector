<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveTraitRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
    use TraitToBeRemoved;
}

?>
-----
<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveTraitRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
}

?>
