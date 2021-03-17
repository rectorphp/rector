<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;

final class ExpectExceptionMessageFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeComparator
     */
    private $nodeComparator;

    /**
     * @var ArgumentShiftingFactory
     */
    private $argumentShiftingFactory;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ArgumentShiftingFactory $argumentShiftingFactory,
        NodeComparator $nodeComparator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->argumentShiftingFactory = $argumentShiftingFactory;
        $this->nodeComparator = $nodeComparator;
    }

    public function create(MethodCall $methodCall, Variable $exceptionVariable): ?MethodCall
    {
        if (! $this->nodeNameResolver->isLocalMethodCallsNamed($methodCall, ['assertSame', 'assertEquals'])) {
            return null;
        }

        $secondArgument = $methodCall->args[1]->value;
        if (! $secondArgument instanceof MethodCall) {
            return null;
        }

        if (! $this->nodeComparator->areNodesEqual($secondArgument->var, $exceptionVariable)) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($secondArgument->name, 'getMessage')) {
            return null;
        }

        $this->argumentShiftingFactory->removeAllButFirstArgMethodCall($methodCall, 'expectExceptionMessage');
        return $methodCall;
    }
}
