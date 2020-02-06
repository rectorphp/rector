<?php

namespace Rector\Core\Tests\Rector\ClassLike\RemoveTraitRector\Fixture;

use Rector\Core\Tests\Rector\ClassLike\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
    use TraitToBeRemoved;
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\ClassLike\RemoveTraitRector\Fixture;

use Rector\Core\Tests\Rector\ClassLike\RemoveTraitRector\Source\TraitToBeRemoved;

class SomeClass
{
}

?>
