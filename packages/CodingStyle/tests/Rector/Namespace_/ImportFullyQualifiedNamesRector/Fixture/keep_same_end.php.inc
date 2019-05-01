<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Some\Trait_;

final class SameEnd
{
    /**
     * @param \Some\Trait_ $firstTrait
     * @param Another\Trait_ $secondTrait
     * @param \Some\Trait_ $thirdTrait
     */
    public function __construct(\Some\Trait_ $firstTrait, Another\Trait_ $secondTrait, \Some\Trait_ $thirdTrait)
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Some\Trait_;

final class SameEnd
{
    /**
     * @param Trait_ $firstTrait
     * @param Another\Trait_ $secondTrait
     * @param Trait_ $thirdTrait
     */
    public function __construct(Trait_ $firstTrait, Another\Trait_ $secondTrait, Trait_ $thirdTrait)
    {
    }
}

?>
