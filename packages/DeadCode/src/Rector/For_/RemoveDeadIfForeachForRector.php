<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\For_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveDeadIfForeachForRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove if, foreach and for that does not do anything', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($someObject)
    {
        $value = 5;
        if ($value) {
        }

        if ($someObject->run()) {
        }

        foreach ($values as $value) {
        }

        return $value;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($someObject)
    {
        $value = 5;
        if ($someObject->run()) {
        }

        return $value;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [For_::class, If_::class, Foreach_::class];
    }

    /**
     * @param For_|If_|Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof If_) {
            return $this->processIf($node);
        }

        if ($node instanceof Foreach_) {
            return $this->processForeach($node);
        }

        if ($node instanceof For_) {
            if ($node->stmts !== []) {
                return null;
            }

            $this->removeNode($node);

            return null;
        }

        return $node;
    }

    private function processIf(If_ $if): ?If_
    {
        if ($if->stmts !== []) {
            return null;
        }

        if ($if->else !== null) {
            return null;
        }

        if ($if->elseifs !== []) {
            return null;
        }

        if ($this->isNodeWithSideEffect($if->cond)) {
            return null;
        }

        $this->removeNode($if);

        return null;
    }

    private function processForeach(Foreach_ $node): ?Foreach_
    {
        if ($node->stmts !== []) {
            return null;
        }

        if ($this->isNodeWithSideEffect($node->expr)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function isNodeWithSideEffect(Expr $expr): bool
    {
        if ($expr instanceof Variable) {
            return false;
        }

        if ($expr instanceof Scalar) {
            return false;
        }
        return ! $this->isBool($expr);
    }
}
