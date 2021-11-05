<?php

declare(strict_types=1);

namespace Rector\Tests\NameImporting\NodeAnalyzer\UseAnalyzer\Fixture;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\UnionType as PhpParserUnionType;

final class UnionAliasImports
{
    public function param(String_ | PhpParserUnionType $node)
    {
    }
}

