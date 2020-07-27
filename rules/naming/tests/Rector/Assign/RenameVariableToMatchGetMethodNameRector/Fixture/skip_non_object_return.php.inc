<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class SkipNonObjectReturn
{
    public function run(\PhpParser\Node $node)
    {
        $value = $node->getAttribute('item');
    }
}

?>
