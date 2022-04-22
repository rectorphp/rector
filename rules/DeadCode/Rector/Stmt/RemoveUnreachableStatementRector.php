<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
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
     * @readonly
     * @var \Rector\DeadCode\SideEffect\SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;
    public function __construct(\Rector\DeadCode\SideEffect\SideEffectNodeDetector $sideEffectNodeDetector)
    {
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
    }
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
        return [\PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\FunctionLike::class, \PhpParser\Node\Stmt\Else_::class, \PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param Foreach_|FunctionLike|Else_|If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $stmts = $node->stmts;
        $isPassedTheUnreachable = \false;
        $toBeRemovedKeys = [];
        foreach ($stmts as $key => $stmt) {
            if ($this->shouldSkipNode($stmt)) {
                continue;
            }
            if ($this->isUnreachable($stmt)) {
                $isPassedTheUnreachable = \true;
            }
            if ($this->isAfterMarkTestSkippedMethodCall($stmt)) {
                // keep for test case temporary ignored
                break;
            }
            if (!$isPassedTheUnreachable) {
                continue;
            }
            $toBeRemovedKeys[] = $key;
        }
        if ($toBeRemovedKeys === []) {
            return null;
        }
        $start = \reset($toBeRemovedKeys);
        if (!isset($stmts[$start - 1])) {
            return null;
        }
        $previousFirstUnreachable = $stmts[$start - 1];
        if (\in_array(\get_class($previousFirstUnreachable), [\PhpParser\Node\Stmt\Throw_::class, \PhpParser\Node\Stmt\Return_::class, \PhpParser\Node\Expr\Exit_::class], \true)) {
            return $this->processCleanUpUnreachabelStmts($node, $toBeRemovedKeys);
        }
        // check previous side effect can check against start jump key - 2
        // as previously already checked as reachable part
        if (!$this->hasPreviousSideEffect($start - 2, $stmts)) {
            return $this->processCleanUpUnreachabelStmts($node, $toBeRemovedKeys);
        }
        return null;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function hasPreviousSideEffect(int $start, array $stmts) : bool
    {
        for ($key = $start; $key > 0; --$key) {
            $previousStmt = $stmts[$key];
            $hasSideEffect = (bool) $this->betterNodeFinder->findFirst($previousStmt, function (\PhpParser\Node $node) : bool {
                return $this->sideEffectNodeDetector->detectCallExpr($node);
            });
            if ($hasSideEffect) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param int[] $toBeRemovedKeys
     * @param \PhpParser\Node\FunctionLike|\PhpParser\Node\Stmt\Else_|\PhpParser\Node\Stmt\Foreach_|\PhpParser\Node\Stmt\If_ $node
     */
    private function processCleanUpUnreachabelStmts($node, array $toBeRemovedKeys) : \PhpParser\Node
    {
        foreach ($toBeRemovedKeys as $toBeRemovedKey) {
            unset($node->stmts[$toBeRemovedKey]);
        }
        return $node;
    }
    private function shouldSkipNode(\PhpParser\Node\Stmt $stmt) : bool
    {
        return $stmt instanceof \PhpParser\Node\Stmt\Nop;
    }
    private function isUnreachable(\PhpParser\Node\Stmt $stmt) : bool
    {
        $isUnreachable = $stmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE);
        return $isUnreachable === \true;
    }
    /**
     * Keep content after markTestSkipped(), intentional temporary
     */
    private function isAfterMarkTestSkippedMethodCall(\PhpParser\Node\Stmt $stmt) : bool
    {
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return \false;
        }
        $expr = $stmt->expr;
        if ($expr instanceof \PhpParser\Node\Expr\MethodCall || $expr instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->isNames($expr->name, ['markTestSkipped', 'markTestIncomplete']);
        }
        return \false;
    }
}
