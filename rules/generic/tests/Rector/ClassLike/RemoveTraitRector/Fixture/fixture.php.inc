<?php

namespace Rector\Generic\Tests\Rector\ClassLike\RemoveTraitRector\Fixture;

use Rector\Generic\Tests\Rector\ClassLike\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
    use TraitToBeRemoved;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassLike\RemoveTraitRector\Fixture;

use Rector\Generic\Tests\Rector\ClassLike\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
}

?>
