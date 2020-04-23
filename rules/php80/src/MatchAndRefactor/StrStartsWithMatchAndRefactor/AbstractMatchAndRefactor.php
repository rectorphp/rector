<?php

declare(strict_types=1);

namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\ValueObject\StrStartsWithValueObject;

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

    /**
     * @return FuncCall|BooleanNot
     */
    protected function createStrStartsWith(StrStartsWithValueObject $strStartsWithValueObject): Node
    {
        $args = [
            new Arg($strStartsWithValueObject->getHaystackExpr()),
            new Arg($strStartsWithValueObject->getNeedleExpr()),
        ];

        $funcCall = new FuncCall(new Name('str_starts_with'), $args);
        if ($strStartsWithValueObject->isPositive()) {
            return $funcCall;
        }

        return new BooleanNot($funcCall);
    }

    protected function createStrStartsWithValueObjectFromFuncCall(
        FuncCall $funcCall,
        bool $isPositive
    ): StrStartsWithValueObject {
        $haystack = $funcCall->args[0]->value;
        $needle = $funcCall->args[1]->value;

        return new StrStartsWithValueObject($funcCall, $haystack, $needle, $isPositive);
    }
}
