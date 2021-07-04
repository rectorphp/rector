<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\SomeTrait;

final class SameEndWithExistingImport
{
    public function __construct(\Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\SomeTrait $firstTrait)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Some\SomeTrait;

final class SameEndWithExistingImport
{
    public function __construct(SomeTrait $firstTrait)
    {
    }
}

?>
