<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

final class SkipReuseVariableInAssignExpr
{
    public function run()
    {
        $continuation = [];
        $continuation['descriptions'] = ['a'];
        $continuation['descriptions'] = \array_map(function ($description) {
            $description = 'b';
            return $description;
        }, $continuation['descriptions']);

        return $continuation;
    }
}
