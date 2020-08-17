<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class KeepReUse
{
    public function resolveFirstTypes(Expr $node)
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope === null) {
            return [];
        }

        /** @var Scope $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);

        return $nodeScope->getType($node);
    }
}
