<?php

declare (strict_types=1);
namespace Rector\CodingStyle\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
final class ImplodeAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    /**
     * Matches: "implode('","', $items)"
     */
    public function isImplodeToJson(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr, 'implode')) {
            return \false;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($expr->args, 1)) {
            return \false;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($expr->args, 0)) {
            return \false;
        }
        /** @var Arg $firstArg */
        $firstArg = $expr->args[0];
        $firstArgumentValue = $firstArg->value;
        if (!$firstArgumentValue instanceof \PhpParser\Node\Scalar\String_) {
            return \true;
        }
        return $firstArgumentValue->value === '","';
    }
}
