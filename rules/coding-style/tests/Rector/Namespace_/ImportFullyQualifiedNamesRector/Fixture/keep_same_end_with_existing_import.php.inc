<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_;

final class SameEndWithExistingImport
{
    public function __construct(\Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_ $firstTrait)
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\Trait_;

final class SameEndWithExistingImport
{
    public function __construct(Trait_ $firstTrait)
    {
    }
}

?>
