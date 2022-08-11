<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
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
    public function detect(FuncCall $funcCall, string $functionName) : bool
    {
        /** @var If_|null $firstParentIf */
        $firstParentIf = $this->betterNodeFinder->findParentType($funcCall, If_::class);
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
