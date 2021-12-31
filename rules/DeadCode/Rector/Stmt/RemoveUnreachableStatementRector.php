<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/phpstan/phpstan/blob/83078fe308a383c618b8c1caec299e5765d9ac82/src/Node/UnreachableStatementNode.php
 *
 * @see \Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\RemoveUnreachableStatementRectorTest
 */
final class RemoveUnreachableStatementRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<class-string<Node>>
     */
    private const STMTS_WITH_IS_UNREACHABLE = [\PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\Do_::class];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unreachable statements', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5;

        $removeMe = 10;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt::class];
    }
    /**
     * @param Stmt $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipNode($node)) {
            return null;
        }
        // might be PHPStan false positive, better skip
        $previousStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
        if ($previousStatement instanceof \PhpParser\Node\Stmt && \in_array(\get_class($previousStatement), self::STMTS_WITH_IS_UNREACHABLE, \true)) {
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE, $previousStatement->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE));
        }
        if (!$this->isUnreachable($node)) {
            return null;
        }
        if ($this->isAfterMarkTestSkippedMethodCall($node)) {
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE, \false);
            return null;
        }
        $this->removeNode($node);
        return null;
    }
    private function shouldSkipNode(\PhpParser\Node\Stmt $stmt) : bool
    {
        if ($stmt instanceof \PhpParser\Node\Stmt\Nop) {
            return \true;
        }
        if ($stmt instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \true;
        }
        return $stmt instanceof \PhpParser\Node\FunctionLike;
    }
    private function isUnreachable(\PhpParser\Node\Stmt $stmt) : bool
    {
        $isUnreachable = $stmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE);
        if ($isUnreachable === \true) {
            return \true;
        }
        // traverse up for unreachable node in the same scope
        $previousNode = $stmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
        while ($previousNode instanceof \PhpParser\Node\Stmt && !$this->isBreakingScopeNode($previousNode)) {
            $isUnreachable = $previousNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE);
            if ($isUnreachable === \true) {
                return \true;
            }
            $previousNode = $previousNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
        }
        return \false;
    }
    /**
     * Keep content after markTestSkipped(), intentional temporary
     */
    private function isAfterMarkTestSkippedMethodCall(\PhpParser\Node\Stmt $stmt) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($stmt, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall && !$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return \false;
            }
            if ($node->name instanceof \PhpParser\Node\Expr\MethodCall) {
                return \false;
            }
            if ($node->name instanceof \PhpParser\Node\Expr\StaticCall) {
                return \false;
            }
            return $this->isNames($node->name, ['markTestSkipped', 'markTestIncomplete']);
        });
    }
    /**
     * Check nodes that breaks scope while traversing up
     */
    private function isBreakingScopeNode(\PhpParser\Node\Stmt $stmt) : bool
    {
        if ($stmt instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \true;
        }
        if ($stmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \true;
        }
        if ($stmt instanceof \PhpParser\Node\Stmt\Function_) {
            return \true;
        }
        if ($stmt instanceof \PhpParser\Node\Stmt\Namespace_) {
            return \true;
        }
        return $stmt instanceof \PhpParser\Node\Stmt\Else_;
    }
}
