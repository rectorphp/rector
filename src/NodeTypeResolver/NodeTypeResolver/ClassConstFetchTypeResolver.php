<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @implements NodeTypeResolverInterface<ClassConstFetch>
 */
final class ClassConstFetchTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function resolve(Node $node) : Type
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new MixedType();
        }
        if ($node->class instanceof FullyQualified) {
            return $scope->getType($node);
        }
        if ($node->class instanceof Name && $node->class->hasAttribute(AttributeKey::NAMESPACED_NAME)) {
            $newNode = clone $node;
            $newNode->class = new FullyQualified($node->class->getAttribute(AttributeKey::NAMESPACED_NAME));
            return $scope->getType($newNode);
        }
        return $scope->getType($node);
    }
}
