<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class PregMatchTypeCorrector
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
     * @var \Rector\NodeNestingScope\ParentScopeFinder
     */
    private $parentScopeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, ParentScopeFinder $parentScopeFinder, NodeComparator $nodeComparator, ArgsAnalyzer $argsAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parentScopeFinder = $parentScopeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    /**
     * Special case for "preg_match(), preg_match_all()" - with 3rd argument
     * @see https://github.com/rectorphp/rector/issues/786
     */
    public function correct(Expr $expr, Type $originalType) : Type
    {
        if (!$expr instanceof Variable) {
            return $originalType;
        }
        if ($originalType instanceof ArrayType) {
            return $originalType;
        }
        $variableUsages = $this->getVariableUsages($expr);
        foreach ($variableUsages as $variableUsage) {
            $possiblyArg = $variableUsage->getAttribute(AttributeKey::PARENT_NODE);
            if (!$possiblyArg instanceof Arg) {
                continue;
            }
            $funcCallNode = $possiblyArg->getAttribute(AttributeKey::PARENT_NODE);
            if (!$funcCallNode instanceof FuncCall) {
                continue;
            }
            if (!$this->nodeNameResolver->isNames($funcCallNode, ['preg_match', 'preg_match_all'])) {
                continue;
            }
            if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($funcCallNode->args, 2)) {
                continue;
            }
            /** @var Arg $thirdArg */
            $thirdArg = $funcCallNode->args[2];
            // are the same variables
            if (!$this->nodeComparator->areNodesEqual($thirdArg->value, $expr)) {
                continue;
            }
            return new ArrayType(new MixedType(), new MixedType());
        }
        return $originalType;
    }
    /**
     * @return Node[]
     */
    private function getVariableUsages(Variable $variable) : array
    {
        $scope = $this->parentScopeFinder->find($variable);
        if ($scope === null) {
            return [];
        }
        return $this->betterNodeFinder->find((array) $scope->stmts, static function (Node $node) use($variable) : bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $node->name === $variable->name;
        });
    }
}
