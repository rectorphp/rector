<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class SkipWithArrayMixed
{
    /**
     * @param object|array<mixed> $foo
     * @param array<mixed> $bar
     */
    public function run(object|array $foo, array $bar): int
    {
        return 5;
    }
}
