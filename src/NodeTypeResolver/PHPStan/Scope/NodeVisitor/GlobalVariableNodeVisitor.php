<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class GlobalVariableNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof StmtsAwareInterface) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        /** @var string[] $globalVariableNames */
        $globalVariableNames = [];
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Global_) {
                $this->setIsGlobalVarAttribute($stmt, $globalVariableNames);
                continue;
            }
            foreach ($stmt->vars as $variable) {
                if ($variable instanceof Variable && !$variable->name instanceof Expr) {
                    $variable->setAttribute(AttributeKey::IS_GLOBAL_VAR, \true);
                    $globalVariableNames[] = $variable->name;
                }
            }
        }
        return null;
    }
    /**
     * @param string[] $globalVariableNames
     */
    private function setIsGlobalVarAttribute(Stmt $stmt, array $globalVariableNames) : void
    {
        if ($globalVariableNames === []) {
            return;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, static function (Node $subNode) use($globalVariableNames) {
            if ($subNode instanceof Class_) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof Variable) {
                return null;
            }
            if ($subNode->name instanceof Expr) {
                return null;
            }
            if (!\in_array($subNode->name, $globalVariableNames, \true)) {
                return null;
            }
            $subNode->setAttribute(AttributeKey::IS_GLOBAL_VAR, \true);
            return $subNode;
        });
    }
}
