<?php

namespace Rector\Tests\Rector\ClassLike\RemoveTraitRector\Fixture;

use Rector\Tests\Rector\ClassLike\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
    use TraitToBeRemoved;
}

?>
-----
<?php

namespace Rector\Tests\Rector\ClassLike\RemoveTraitRector\Fixture;

use Rector\Tests\Rector\ClassLike\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
}

?>
