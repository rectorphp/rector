<?php
namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

class SkipAliasedNames
{
    /**
     * @param Assert\Choice $choice
     */
    public function __construct(Assert\Choice $choice)
    {
        $constraint = Assert\Blank::class;
    }
}
?>
