<?php

declare(strict_types=1);

namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;

abstract class AbstractMatchAndRefactor
{
    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    protected $valueResolver;

    /**
     * @var BetterStandardPrinter
     */
    protected $betterStandardPrinter;

    /**
     * @required
     */
    public function autowireAbstractMatchAndRefactor(
        NodeNameResolver $nodeNameResolver,
        ValueResolver $valueResolver,
        BetterStandardPrinter $betterStandardPrinter
    ): void {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    protected function isFuncCallName(Node $node, string $name): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        return $this->nodeNameResolver->isName($node, $name);
    }
}
