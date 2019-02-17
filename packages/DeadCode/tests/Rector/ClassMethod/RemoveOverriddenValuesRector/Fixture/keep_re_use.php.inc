<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\Attribute;

final class KeepReUse
{
    public function resolveFirstTypes(Expr $node)
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        if ($nodeScope === null) {
            return [];
        }

        /** @var Scope $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);

        return $nodeScope->getType($node);
    }
}
