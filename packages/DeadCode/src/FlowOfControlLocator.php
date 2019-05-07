<?php declare(strict_types=1);

namespace Rector\DeadCode;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FlowOfControlLocator
{
    public function resolveNestingHashFromFunctionLike(FunctionLike $functionLike, Node $checkedNode): string
    {
        $nestingHash = '_';

        $parentNode = $checkedNode;
        while ($parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE)) {
            if ($parentNode instanceof Expression) {
                continue;
            }

            $nestingHash .= spl_object_hash($parentNode);
            if ($functionLike === $parentNode) {
                return $nestingHash;
            }
        }

        return $nestingHash;
    }
}
