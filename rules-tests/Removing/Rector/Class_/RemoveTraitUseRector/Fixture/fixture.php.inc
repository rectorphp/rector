<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveTraitUseRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveTraitUseRector\Source\TraitToBeRemoved;

class SomeClass
{
    use TraitToBeRemoved;
}

?>
-----
<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveTraitUseRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveTraitUseRector\Source\TraitToBeRemoved;

class SomeClass
{
}

?>
