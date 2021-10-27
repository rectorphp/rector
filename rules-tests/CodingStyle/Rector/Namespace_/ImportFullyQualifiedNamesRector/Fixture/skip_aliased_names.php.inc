<?php
namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

class SkipAliasedNames
{
    public function __construct()
    {
        $constraint = Assert\Blank::class;
    }
}
?>
-----
<?php
namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints as Assert;

class SkipAliasedNames
{
    public function __construct()
    {
        $constraint = Blank::class;
    }
}
?>
