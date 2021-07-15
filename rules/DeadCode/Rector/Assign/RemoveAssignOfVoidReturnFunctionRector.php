<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\VoidType;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\RemoveAssignOfVoidReturnFunctionRectorTest
 */
final class RemoveAssignOfVoidReturnFunctionRector extends AbstractRector
{
    public function __construct(
        private ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove assign of void function/method to variable',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = $this->getOne();
    }

    private function getOne(): void
    {
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->getOne();
    }

    private function getOne(): void
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof FuncCall && ! $node->expr instanceof MethodCall && ! $node->expr instanceof StaticCall) {
            return null;
        }

        $exprType = $this->nodeTypeResolver->resolve($node->expr);
        if (! $exprType instanceof VoidType) {
            return null;
        }

        if ($this->exprUsedInNextNodeAnalyzer->isUsed($node->var)) {
            return null;
        }

        return $node->expr;
    }
}
