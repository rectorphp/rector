<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Closure\RemoveUnusedClosureVariableUseRector\RemoveUnusedClosureVariableUseRectorTest
 */
final class RemoveUnusedClosureVariableUseRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused variable in use() of closure', [new CodeSample(<<<'CODE_SAMPLE'
$var = 1;

$closure = function() use ($var) {
    echo 'Hello World';
};

CODE_SAMPLE
, <<<'CODE_SAMPLE'
$var = 1;
$closure = function() {
    echo 'Hello World';
};

CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->uses === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->uses as $key => $useVariable) {
            $useVariableName = $this->getName($useVariable->var);
            if (!is_string($useVariableName)) {
                continue;
            }
            $isUseUsed = (bool) $this->betterNodeFinder->findFirst($node->stmts, fn(Node $subNode): bool => $this->exprUsedInNodeAnalyzer->isUsed($subNode, $useVariable->var));
            if ($isUseUsed) {
                continue;
            }
            unset($node->uses[$key]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
