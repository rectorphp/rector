<?php
namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

class SkipAliasedNamesDocblock
{
    /**
     * @var Assert\Choice
     */
    public $someProperty;
}
?>
