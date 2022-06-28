<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class RegexFuncAnalyzer
{
    /**
     * @var string[]
     */
    private const REGEX_FUNCTION_NAMES = ['preg_match', 'preg_match_all'];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, ValueResolver $valueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->valueResolver = $valueResolver;
    }
    public function isRegexFunctionNames(FuncCall $funcCall) : bool
    {
        if ($this->nodeNameResolver->isNames($funcCall, self::REGEX_FUNCTION_NAMES)) {
            return \true;
        }
        $variable = $funcCall->name;
        if (!$variable instanceof Variable) {
            return \false;
        }
        /** @var Assign|null $assignExprVariable */
        $assignExprVariable = $this->betterNodeFinder->findFirstPrevious($funcCall, function (Node $node) use($variable) : bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->var, $variable);
        });
        if (!$assignExprVariable instanceof Assign) {
            return \false;
        }
        $expr = $assignExprVariable->expr;
        if (!$expr instanceof Ternary) {
            return \false;
        }
        if (!$expr->if instanceof Expr) {
            return \false;
        }
        if (!$this->valueResolver->isValues($expr->if, self::REGEX_FUNCTION_NAMES)) {
            return \false;
        }
        return $this->valueResolver->isValues($expr->else, self::REGEX_FUNCTION_NAMES);
    }
}
