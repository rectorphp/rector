<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPrefix202512\Webmozart\Assert\Assert;
final class StaticVariableNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function enterNode(Node $node): ?Node
    {
        if (!NodeGroup::isStmtAwareNode($node)) {
            return null;
        }
        Assert::propertyExists($node, 'stmts');
        if ($node->stmts === null) {
            return null;
        }
        /** @var string[] $staticVariableNames */
        $staticVariableNames = [];
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Static_) {
                $this->setIsStaticVarAttribute($stmt, $staticVariableNames);
                continue;
            }
            foreach ($stmt->vars as $staticVar) {
                $staticVariableName = $staticVar->var->name;
                if (!is_string($staticVariableName)) {
                    continue;
                }
                $staticVar->var->setAttribute(AttributeKey::IS_STATIC_VAR, \true);
                $staticVariableNames[] = $staticVariableName;
            }
        }
        return null;
    }
    /**
     * @param string[] $staticVariableNames
     */
    private function setIsStaticVarAttribute(Stmt $stmt, array $staticVariableNames): void
    {
        if ($staticVariableNames === []) {
            return;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, static function (Node $subNode) use ($staticVariableNames) {
            if ($subNode instanceof Class_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof Variable) {
                return null;
            }
            if ($subNode->name instanceof Expr) {
                return null;
            }
            if (!in_array($subNode->name, $staticVariableNames, \true)) {
                return null;
            }
            $subNode->setAttribute(AttributeKey::IS_STATIC_VAR, \true);
            return $subNode;
        });
    }
}
