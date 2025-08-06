<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\Enum\NativeFuncCallPositions;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FuncCall\AddArrayFunctionClosureParamTypeRector\AddArrayFunctionClosureParamTypeRectorTest
 */
final class AddArrayFunctionClosureParamTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add array_filter()/array_map() function closure param type, based on passed iterable', [new CodeSample(<<<'CODE_SAMPLE'
$items = [1, 2, 3];
$result = array_filter($items, fn ($item) => $item > 1);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$items = [1, 2, 3];
$result = array_filter($items, fn (int $item) => $item > 1
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
        foreach (NativeFuncCallPositions::ARRAY_AND_CALLBACK_POSITIONS as $functionName => $positions) {
            if (!$this->isName($node, $functionName)) {
                continue;
            }
            if ($node->isFirstClassCallable()) {
                continue;
            }
            $arrayPosition = $positions['array'];
            $callbackPosition = $positions['callback'];
            $firstArgExpr = $node->getArgs()[$callbackPosition]->value;
            if (!$firstArgExpr instanceof ArrowFunction && !$firstArgExpr instanceof Closure) {
                continue;
            }
            $arrowFunction = $firstArgExpr;
            $arrowFunctionParam = $arrowFunction->getParams()[0];
            // param is known already
            if ($arrowFunctionParam->type instanceof Node) {
                continue;
            }
            $passedExprType = $this->getType($node->getArgs()[$arrayPosition]->value);
            if ($passedExprType instanceof ConstantArrayType || $passedExprType instanceof ArrayType) {
                $singlePassedExprType = $passedExprType->getItemType();
                if ($singlePassedExprType instanceof MixedType) {
                    continue;
                }
                $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($singlePassedExprType, TypeKind::PARAM);
                if (!$paramType instanceof Node) {
                    continue;
                }
                $arrowFunctionParam->type = $paramType;
                return $node;
            }
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
}
