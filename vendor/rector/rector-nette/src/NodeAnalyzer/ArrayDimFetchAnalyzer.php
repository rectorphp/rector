<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class ArrayDimFetchAnalyzer
{
    public function isBeingAssignedOrInitialized(ArrayDimFetch $arrayDimFetch) : bool
    {
        $parent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof Assign) {
            return \false;
        }
        if ($parent->var === $arrayDimFetch) {
            return \true;
        }
        return $parent->expr === $arrayDimFetch;
    }
}
