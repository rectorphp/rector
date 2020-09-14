<?php

declare(strict_types=1);

namespace Rector\Polyfill\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Polyfill\ConditionEvaluator;
use Rector\Polyfill\ConditionResolver;

/**
 * @see https://www.php.net/manual/en/function.version-compare.php
 *
 * @see \Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\UnwrapFutureCompatibleIfPhpVersionRectorTest
 */
final class UnwrapFutureCompatibleIfPhpVersionRector extends AbstractRector
{
    /**
     * @var ConditionEvaluator
     */
    private $conditionEvaluator;

    /**
     * @var ConditionResolver
     */
    private $conditionResolver;

    public function __construct(ConditionEvaluator $conditionEvaluator, ConditionResolver $conditionResolver)
    {
        $this->conditionEvaluator = $conditionEvaluator;
        $this->conditionResolver = $conditionResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove php version checks if they are passed', [
            new CodeSample(
                <<<'CODE_SAMPLE'
// current PHP: 7.2
if (version_compare(PHP_VERSION, '7.2', '<')) {
    return 'is PHP 7.1-';
} else {
    return 'is PHP 7.2+';
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
// current PHP: 7.2
return 'is PHP 7.2+';
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ((bool) $node->elseifs) {
            return null;
        }

        $condition = $this->conditionResolver->resolveFromExpr($node->cond);
        if ($condition === null) {
            return null;
        }

        $result = $this->conditionEvaluator->evaluate($condition);
        if ($result === null) {
            return null;
        }

        // if is skipped
        if ($result) {
            $this->refactorIsMatch($node);
        } else {
            $this->refactorIsNotMatch($node);
        }

        return $node;
    }

    private function refactorIsMatch(If_ $if): void
    {
        if ((bool) $if->elseifs) {
            return;
        }

        $this->unwrapStmts($if->stmts, $if);

        $this->removeNode($if);
    }

    private function refactorIsNotMatch(If_ $if): void
    {
        // no else → just remove the node
        if ($if->else === null) {
            $this->removeNode($if);
            return;
        }

        // else is always used
        $this->unwrapStmts($if->else->stmts, $if);

        $this->removeNode($if);
    }
}
