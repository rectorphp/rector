<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;

final class ExpectExceptionMessageRegExpFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ArgumentShiftingFactory
     */
    private $argumentShiftingFactory;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ArgumentShiftingFactory $argumentShiftingFactory,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->argumentShiftingFactory = $argumentShiftingFactory;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function create(MethodCall $methodCall, Variable $exceptionVariable): ?MethodCall
    {
        if (! $this->nodeNameResolver->isLocalMethodCallNamed($methodCall, 'assertContains')) {
            return null;
        }

        $secondArgument = $methodCall->args[1]->value;
        if (! $secondArgument instanceof MethodCall) {
            return null;
        }

        // looking for "$exception->getMessage()"
        if (! $this->betterStandardPrinter->areNodesEqual($secondArgument->var, $exceptionVariable)) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($secondArgument->name, 'getMessage')) {
            return null;
        }

        $expectExceptionMessageRegExpMethodCall = $this->argumentShiftingFactory->createFromMethodCall(
            $methodCall,
            'expectExceptionMessageRegExp'
        );

        // put regex between "#...#" to create match
        if ($expectExceptionMessageRegExpMethodCall->args[0]->value instanceof String_) {
            /** @var String_ $oldString */
            $oldString = $methodCall->args[0]->value;
            $expectExceptionMessageRegExpMethodCall->args[0]->value = new String_('#' . preg_quote(
                $oldString->value,
                '#'
            ) . '#');
        }

        return $expectExceptionMessageRegExpMethodCall;
    }
}
