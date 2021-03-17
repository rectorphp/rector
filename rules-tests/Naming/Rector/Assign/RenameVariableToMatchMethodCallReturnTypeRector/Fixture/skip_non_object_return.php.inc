<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipNonObjectReturn
{
    public function run(\PhpParser\Node $node)
    {
        $value = $node->getAttribute('item');
    }
}

?>
