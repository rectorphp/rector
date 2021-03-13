<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipForeachAssign
{
    public function run(array $nodes)
    {
        $abstracts = array();

        // Abstract numbering definition
        if (true) {
            foreach ($nodes as $node) {
                $abstract = &$abstracts[$node];
            }
        }
    }
}
