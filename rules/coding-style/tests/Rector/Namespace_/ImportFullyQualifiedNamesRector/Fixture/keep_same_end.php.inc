<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Another;
use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_;

final class SameEnd
{
    /**
     * @param \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $firstTrait
     * @param Another\Trait_ $secondTrait
     * @param \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $thirdTrait
     */
    public function __construct(\Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $firstTrait, Another\Trait_ $secondTrait, \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $thirdTrait)
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Another;
use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_;

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
