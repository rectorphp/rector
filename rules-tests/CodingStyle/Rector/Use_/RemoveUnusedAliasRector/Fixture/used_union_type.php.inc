<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\UnionType as PhpParserUnionType;

final class UsedUnionType
{
    public function param(String_ | PhpParserUnionType $node)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\UnionType;

final class UsedUnionType
{
    public function param(String_ | UnionType $node)
    {
    }
}

?>
