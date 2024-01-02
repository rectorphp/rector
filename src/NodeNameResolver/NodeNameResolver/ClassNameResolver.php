<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<ClassLike>
 */
final class ClassNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return ClassLike::class;
    }
    /**
     * @param ClassLike $node
     */
    public function resolve(Node $node, ?Scope $scope) : ?string
    {
        if ($node->namespacedName instanceof Name) {
            return $node->namespacedName->toString();
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        return $node->name->toString();
    }
}
