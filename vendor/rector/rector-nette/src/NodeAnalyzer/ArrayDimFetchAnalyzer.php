<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ArrayDimFetchAnalyzer
{
    public function isBeingAssignedOrInitialized(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        $parent = $arrayDimFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        if ($parent->var === $arrayDimFetch) {
            return \true;
        }
        return $parent->expr === $arrayDimFetch;
    }
}
