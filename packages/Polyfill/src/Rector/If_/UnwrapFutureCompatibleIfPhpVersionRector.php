<?php

declare(strict_types=1);

namespace Rector\Polyfill\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Polyfill\ConditionEvaluator;
use Rector\Polyfill\ConditionResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
                <<<'PHP'
// current PHP: 7.2
if (version_compare(PHP_VERSION, '7.2', '<')) {
    return 'is PHP 7.1-';
} else {
    return 'is PHP 7.2+';
}
PHP
,
                <<<'PHP'
// current PHP: 7.2
return 'is PHP 7.2+';
PHP

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
        // has some elseif, we need to check them too later @todo
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

    private function refactorIsMatch(If_ $if): void
    {
        // has some elseif, we need to check them too @todo
        if ((bool) $if->elseifs) {
            return;
        }

        $this->unwrapStmts($if->stmts, $if);

        $this->removeNode($if);
    }
}
