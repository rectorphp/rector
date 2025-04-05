<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FuncCall\AddArrowFunctionParamArrayWhereDimFetchRector\AddArrowFunctionParamArrayWhereDimFetchRectorTest
 */
final class AddArrowFunctionParamArrayWhereDimFetchRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add function/closure param array type, if dim fetch is inside', [new CodeSample(<<<'CODE_SAMPLE'
$array = [['name' => 'John']];

$result = array_map(fn ($item) => $item['name'], $array);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$array = [['name' => 'John']];

$result = array_map(fn (array $item) => $item['name'], $array);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'array_map')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArgExpr = $node->getArgs()[0]->value;
        if (!$firstArgExpr instanceof ArrowFunction) {
            return null;
        }
        $arrowFunction = $firstArgExpr;
        $arrowFunctionParam = $arrowFunction->getParams()[0];
        // param is known already
        if ($arrowFunctionParam->type instanceof Node) {
            return null;
        }
        if (!$arrowFunction->expr instanceof ArrayDimFetch) {
            return null;
        }
        $var = $arrowFunction->expr;
        while ($var instanceof ArrayDimFetch) {
            $var = $var->var;
        }
        if (!$this->nodeComparator->areNodesEqual($var, $arrowFunctionParam->var)) {
            return null;
        }
        $arrowFunctionParam->type = new Identifier('array');
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
}
