<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PhpParser\Node;
use PhpParser\Node\Arg;
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
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeNestingScope\ParentScopeFinder $parentScopeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
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
    public function correct(\PhpParser\Node $node, \PHPStan\Type\Type $originalType) : \PHPStan\Type\Type
    {
        if (!$node instanceof \PhpParser\Node\Expr\Variable) {
            return $originalType;
        }
        if ($originalType instanceof \PHPStan\Type\ArrayType) {
            return $originalType;
        }
        $variableUsages = $this->getVariableUsages($node);
        foreach ($variableUsages as $variableUsage) {
            $possiblyArg = $variableUsage->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$possiblyArg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            $funcCallNode = $possiblyArg->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$funcCallNode instanceof \PhpParser\Node\Expr\FuncCall) {
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
            if (!$this->nodeComparator->areNodesEqual($thirdArg->value, $node)) {
                continue;
            }
            return new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        }
        return $originalType;
    }
    /**
     * @return Node[]
     */
    private function getVariableUsages(\PhpParser\Node\Expr\Variable $variable) : array
    {
        $scope = $this->parentScopeFinder->find($variable);
        if ($scope === null) {
            return [];
        }
        return $this->betterNodeFinder->find((array) $scope->stmts, function (\PhpParser\Node $node) use($variable) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $node->name === $variable->name;
        });
    }
}
