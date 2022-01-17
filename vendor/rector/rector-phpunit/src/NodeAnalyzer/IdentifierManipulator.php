<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node;
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
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param string[] $renameMethodMap
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod $node
     */
    public function renameNodeWithMap($node, array $renameMethodMap) : void
    {
        $oldNodeMethodName = $this->resolveOldMethodName($node);
        if ($oldNodeMethodName === null) {
            return;
        }
        $node->name = new \PhpParser\Node\Identifier($renameMethodMap[$oldNodeMethodName]);
    }
    /**
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod $node
     */
    private function resolveOldMethodName($node) : ?string
    {
        if ($node instanceof \PhpParser\Node\Expr\StaticCall || $node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->nodeNameResolver->getName($node->name);
        }
        return $this->nodeNameResolver->getName($node);
    }
}
