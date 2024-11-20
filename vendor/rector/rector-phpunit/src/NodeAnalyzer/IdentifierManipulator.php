<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
/**
 * This class renames node identifier, e.g. ClassMethod rename:
 *
 * -public function someMethod()
 * +public function newMethod()
 */
final class IdentifierManipulator
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param array<string, string> $renameMethodMap
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod $node
     */
    public function renameNodeWithMap($node, array $renameMethodMap) : bool
    {
        $oldNodeMethodName = $this->resolveOldMethodName($node);
        if (!\is_string($oldNodeMethodName)) {
            return \false;
        }
        $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);
        return \true;
    }
    /**
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod $node
     */
    private function resolveOldMethodName($node) : ?string
    {
        if ($node instanceof StaticCall || $node instanceof MethodCall) {
            return $this->nodeNameResolver->getName($node->name);
        }
        return $this->nodeNameResolver->getName($node);
    }
}
