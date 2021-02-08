<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;

final class ExpectExceptionMessageFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ArgumentShiftingFactory
     */
    private $argumentShiftingFactory;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        BetterStandardPrinter $betterStandardPrinter,
        ArgumentShiftingFactory $argumentShiftingFactory
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->argumentShiftingFactory = $argumentShiftingFactory;
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

        if (! $this->betterStandardPrinter->areNodesEqual($secondArgument->var, $exceptionVariable)) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($secondArgument->name, 'getMessage')) {
            return null;
        }

        return $this->argumentShiftingFactory->createFromMethodCall($methodCall, 'expectExceptionMessage');
    }
}
