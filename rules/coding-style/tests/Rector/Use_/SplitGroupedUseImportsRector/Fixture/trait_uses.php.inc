<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Source\FirstTrait;
use Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Source\SecondTrait;

class TraitUses
{
    use FirstTrait, SecondTrait;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Source\FirstTrait;
use Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\Source\SecondTrait;

class TraitUses
{
    use FirstTrait;
    use SecondTrait;
}

?>
