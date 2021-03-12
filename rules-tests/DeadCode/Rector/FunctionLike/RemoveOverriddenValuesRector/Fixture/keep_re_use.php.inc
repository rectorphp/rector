<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class KeepReUse
{
    public function resolveFirstTypes(Expr $node)
    {
        /** @var Scope|null $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return [];
        }

        /** @var Scope $nodeScope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        return $scope->getType($node);
    }
}
