<?php

namespace Rector\Removing\Tests\Rector\Class_\RemoveTraitRector\Fixture;

use Rector\Removing\Tests\Rector\Class_\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
    use TraitToBeRemoved;
}

?>
-----
<?php

namespace Rector\Removing\Tests\Rector\Class_\RemoveTraitRector\Fixture;

use Rector\Removing\Tests\Rector\Class_\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
}

?>
