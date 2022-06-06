<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp72\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\Value\ValueResolver;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class FunctionExistsFunCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, ValueResolver $valueResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
    }
    public function detect(Expr $expr, string $functionName) : bool
    {
        /** @var If_|null $firstParentIf */
        $firstParentIf = $this->betterNodeFinder->findParentType($expr, If_::class);
        if (!$firstParentIf instanceof If_) {
            return \false;
        }
        if (!$firstParentIf->cond instanceof FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($firstParentIf->cond, 'function_exists')) {
            return \false;
        }
        /** @var FuncCall $functionExists */
        $functionExists = $firstParentIf->cond;
        if (!isset($functionExists->args[0])) {
            return \false;
        }
        if (!$functionExists->args[0] instanceof Arg) {
            return \false;
        }
        return $this->valueResolver->isValue($functionExists->args[0]->value, $functionName);
    }
}
