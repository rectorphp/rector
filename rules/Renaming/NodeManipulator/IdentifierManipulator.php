<?php

declare(strict_types=1);

namespace Rector\Renaming\NodeManipulator;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Webmozart\Assert\Assert;

/**
 * This class renames node identifier, e.g. ClassMethod rename:
 *
 * -public function someMethod()
 * +public function newMethod()
 */
final class IdentifierManipulator
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @param ClassConstFetch|MethodCall|PropertyFetch|StaticCall|ClassMethod $node
     * @param string[] $renameMethodMap
     */
    public function renameNodeWithMap(Node $node, array $renameMethodMap): void
    {
        Assert::isAnyOf($node, [
            ClassConstFetch::class, MethodCall::class, PropertyFetch::class, StaticCall::class, ClassMethod::class,
        ]);

        $oldNodeMethodName = $this->resolveOldMethodName($node);
        if ($oldNodeMethodName === null) {
            return;
        }

        $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);
    }

    /**
     * @param ClassConstFetch|MethodCall|PropertyFetch|StaticCall|ClassMethod $node
     */
    public function removeSuffix(Node $node, string $suffixToRemove): void
    {
        Assert::isAnyOf(
            $node,
            [ClassConstFetch::class, MethodCall::class, PropertyFetch::class, StaticCall::class, ClassMethod::class]
        );

        $name = $this->nodeNameResolver->getName($node);
        if ($name === null) {
            return;
        }

        $newName = Strings::replace($name, sprintf('#%s$#', $suffixToRemove), '');

        $node->name = new Identifier($newName);
    }

    /**
     * @param ClassConstFetch|MethodCall|PropertyFetch|StaticCall|ClassMethod $node
     */
    private function resolveOldMethodName(Node $node): ?string
    {
        if ($node instanceof StaticCall || $node instanceof MethodCall) {
            return $this->nodeNameResolver->getName($node->name);
        }

        return $this->nodeNameResolver->getName($node);
    }
}
