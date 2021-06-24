<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PhpParser\Node\Arg;
use PhpParser\Node\Param;

final class SkipTemplate
{
    /**
     * @template TItem as Arg|Param
     * @param array<TItem> $args
     * @param array<int, string> $expectedOrderedParams
     * @return array<TItem>
     */
    public function run(array $args): array
    {
        return $args;
    }
}
