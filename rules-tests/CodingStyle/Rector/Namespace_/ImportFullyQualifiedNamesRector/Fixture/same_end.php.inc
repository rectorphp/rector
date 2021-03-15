<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Another;
use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_;

final class SameEnd
{
    /**
     * @param \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $firstTrait
     * @param Another\Trait_ $secondTrait
     * @param \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $thirdTrait
     */
    public function __construct(\Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $firstTrait, Another\Trait_ $secondTrait, \Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $thirdTrait)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Another;
use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_;

final class SameEnd
{
    /**
     * @param Trait_ $firstTrait
     * @param Another\Trait_ $secondTrait
     * @param Trait_ $thirdTrait
     */
    public function __construct(Trait_ $firstTrait, Another\Trait_ $secondTrait, \Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $thirdTrait)
    {
    }
}

?>
