<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPrefix202608\Webmozart\Assert\Assert;
/**
 * Marks variables declared "global $x;" (IS_GLOBAL_VAR) and "static $x;" (IS_STATIC_VAR),
 * then decorates their later uses in the same stmt scope in one shared traversal.
 */
final class LocalVariableScopeNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
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
        /** @var string[] $globalVariableNames */
        $globalVariableNames = [];
        /** @var string[] $staticVariableNames */
        $staticVariableNames = [];
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Global_) {
                foreach ($stmt->vars as $variable) {
                    if ($variable instanceof Variable && !$variable->name instanceof Expr) {
                        $variable->setAttribute(AttributeKey::IS_GLOBAL_VAR, \true);
                        /** @var string $variableName */
                        $variableName = $variable->name;
                        $globalVariableNames[] = $variableName;
                    }
                }
                continue;
            }
            if ($stmt instanceof Static_) {
                foreach ($stmt->vars as $staticVar) {
                    $staticVariableName = $staticVar->var->name;
                    if (!is_string($staticVariableName)) {
                        continue;
                    }
                    $staticVar->var->setAttribute(AttributeKey::IS_STATIC_VAR, \true);
                    $staticVariableNames[] = $staticVariableName;
                }
                continue;
            }
            $this->decorateScopedVariableUses($stmt, $globalVariableNames, $staticVariableNames);
        }
        return null;
    }
    /**
     * @param string[] $globalVariableNames
     * @param string[] $staticVariableNames
     */
    private function decorateScopedVariableUses(Stmt $stmt, array $globalVariableNames, array $staticVariableNames): void
    {
        if ($globalVariableNames === [] && $staticVariableNames === []) {
            return;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, static function (Node $subNode) use ($globalVariableNames, $staticVariableNames) {
            if ($subNode instanceof Class_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof Variable) {
                return null;
            }
            if ($subNode->name instanceof Expr) {
                return null;
            }
            $isDecorated = \false;
            if (in_array($subNode->name, $globalVariableNames, \true)) {
                $subNode->setAttribute(AttributeKey::IS_GLOBAL_VAR, \true);
                $isDecorated = \true;
            }
            if (in_array($subNode->name, $staticVariableNames, \true)) {
                $subNode->setAttribute(AttributeKey::IS_STATIC_VAR, \true);
                $isDecorated = \true;
            }
            return $isDecorated ? $subNode : null;
        });
    }
}
