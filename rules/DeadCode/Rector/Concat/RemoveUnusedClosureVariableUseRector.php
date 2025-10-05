<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Concat;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
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
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
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
            $isUseUsed = (bool) $this->betterNodeFinder->findVariableOfName($node->stmts, $useVariableName);
            if ($isUseUsed) {
                continue;
            }
            unset($node->uses[$key]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            // reset keys, to keep as expected
            $node->uses = array_values($node->uses);
            return $node;
        }
        return null;
    }
}
