<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector\Source\FirstTrait;
use Rector\Tests\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector\Source\SecondTrait;

class TraitUses
{
    use FirstTrait, SecondTrait;
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector\Source\FirstTrait;
use Rector\Tests\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector\Source\SecondTrait;

class TraitUses
{
    use FirstTrait;
    use SecondTrait;
}

?>
