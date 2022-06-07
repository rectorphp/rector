<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @implements NodeNameResolverInterface<Function_>
 */
final class FunctionNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return Function_::class;
    }
    /**
     * @param Function_ $node
     */
    public function resolve(Node $node) : ?string
    {
        $bareName = (string) $node->name;
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return $bareName;
        }
        $namespaceName = $scope->getNamespace();
        if ($namespaceName !== null) {
            return $namespaceName . '\\' . $bareName;
        }
        return $bareName;
    }
}
