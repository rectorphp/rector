<?php

declare(strict_types=1);

namespace Rector\Polyfill\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\If_;
use Rector\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Polyfill\FeatureSupport\FunctionSupportResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector\UnwrapFutureCompatibleIfFunctionExistsRectorTest
 */
final class UnwrapFutureCompatibleIfFunctionExistsRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var FunctionSupportResolver
     */
    private $functionSupportResolver;

    public function __construct(IfManipulator $ifManipulator, FunctionSupportResolver $functionSupportResolver)
    {
        $this->ifManipulator = $ifManipulator;
        $this->functionSupportResolver = $functionSupportResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove functions exists if with else for always existing', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        if (function_exists('session_abort')) {
            session_abort();
        } else {
            session_write_close();
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        session_abort();
    }
}
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
        $match = $this->ifManipulator->isIfOrIfElseWithFunctionCondition($node, 'function_exists');
        if (! $match) {
            return null;
        }

        /** @var FuncCall $funcCall */
        $funcCall = $node->cond;

        $functionToExistName = $this->getValue($funcCall->args[0]->value);
        if (! is_string($functionToExistName)) {
            return null;
        }

        if (! $this->functionSupportResolver->isFunctionSupported($functionToExistName)) {
            return null;
        }

        $this->unwrapStmts($node->stmts, $node);
        $this->removeNode($node);

        return null;
    }
}
