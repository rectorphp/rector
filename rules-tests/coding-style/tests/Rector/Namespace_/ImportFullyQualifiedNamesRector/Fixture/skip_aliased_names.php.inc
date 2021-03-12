<?php
namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

class SkipAliasedNames
{
    public function __construct()
    {
        $constraint = Assert\Blank::class;
    }
}
?>
