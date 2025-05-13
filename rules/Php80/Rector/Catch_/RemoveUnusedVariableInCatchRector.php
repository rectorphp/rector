<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Catch_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector\RemoveUnusedVariableInCatchRectorTest
 */
final class RemoveUnusedVariableInCatchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private StmtsManipulator $stmtsManipulator;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer;
    public function __construct(StmtsManipulator $stmtsManipulator, BetterNodeFinder $betterNodeFinder, ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->stmtsManipulator = $stmtsManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused variable in `catch()`', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable $notUsedThrowable) {
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable) {
        }
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof TryCatch) {
                continue;
            }
            foreach ($stmt->catches as $catch) {
                $caughtVar = $catch->var;
                if (!$caughtVar instanceof Variable) {
                    continue;
                }
                /** @var string $variableName */
                $variableName = $this->getName($caughtVar);
                $isFoundInCatchStmts = (bool) $this->betterNodeFinder->findFirst($catch->stmts, fn(Node $subNode): bool => $this->exprUsedInNodeAnalyzer->isUsed($subNode, $caughtVar));
                if ($isFoundInCatchStmts) {
                    continue;
                }
                if ($this->stmtsManipulator->isVariableUsedInNextStmt($node, $key + 1, $variableName)) {
                    continue;
                }
                $catch->var = null;
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NON_CAPTURING_CATCH;
    }
}
