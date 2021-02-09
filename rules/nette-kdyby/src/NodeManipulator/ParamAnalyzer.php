<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;

final class ParamAnalyzer
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(BetterNodeFinder $betterNodeFinder, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function isParamUsedInClassMethod(ClassMethod $classMethod, Param $param): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use (
            $param
        ): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->betterStandardPrinter->areNodesEqual($node, $param->var);
        });
    }
}
