<?php

namespace Rector\Generic\Tests\Rector\Class_\RemoveTraitRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
    use TraitToBeRemoved;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\RemoveTraitRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
}

?>
