<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

use PhpParser\Node\Expr\Array_;
use Rector\NodeTypeResolver\Node\AttributeKey;

class SkipParentNode
{
    public function run(Array_ $array)
    {
        $parent = $array->getAttribute(AttributeKey::PARENT_NODE);
    }
}
