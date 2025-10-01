<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
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
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add array_filter()/array_map() function closure param type, based on passed iterable', [new CodeSample(<<<'CODE_SAMPLE'
$items = [1, 2, 3];
$result = array_filter($items, fn ($item) => $item > 1);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$items = [1, 2, 3];
$result = array_filter($items, fn (int $item) => $item > 1);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (count($node->getArgs()) !== 2) {
            return null;
        }
        $hasChanged = \false;
        foreach (NativeFuncCallPositions::ARRAY_AND_CALLBACK_POSITIONS as $functionName => $positions) {
            if (!$this->isName($node, $functionName)) {
                continue;
            }
            $arrayPosition = $positions['array'];
            $callbackPosition = $positions['callback'];
            $callbackArg = $node->getArg('callback', $callbackPosition);
            if (!$callbackArg instanceof Arg) {
                continue;
            }
            $callbackArgExpr = $callbackArg->value;
            if (!$callbackArgExpr instanceof ArrowFunction && !$callbackArgExpr instanceof Closure) {
                continue;
            }
            if (count($callbackArgExpr->getParams()) !== 1) {
                continue;
            }
            $arrowFunction = $callbackArgExpr;
            $arrowFunctionParam = $arrowFunction->getParams()[0];
            // param is known already
            if ($arrowFunctionParam->type instanceof Node) {
                continue;
            }
            $arrayArg = $node->getArg('array', $arrayPosition);
            if (!$arrayArg instanceof Arg) {
                continue;
            }
            $passedExprType = $this->getType($arrayArg->value);
            $singlePassedExprType = $this->resolveArrayItemType($passedExprType);
            if (!$singlePassedExprType instanceof Type) {
                continue;
            }
            if ($singlePassedExprType instanceof MixedType) {
                continue;
            }
            $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($singlePassedExprType, TypeKind::PARAM);
            if (!$paramType instanceof Node) {
                continue;
            }
            $hasChanged = \true;
            $arrowFunctionParam->type = $paramType;
        }
        if ($hasChanged === \false) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    private function resolveArrayItemType(Type $mainType): ?Type
    {
        if ($mainType instanceof ConstantArrayType || $mainType instanceof ArrayType) {
            return $mainType->getItemType();
        }
        if ($mainType instanceof IntersectionType) {
            foreach ($mainType->getTypes() as $subType) {
                if ($subType instanceof AccessoryArrayListType) {
                    continue;
                }
                if (!$subType->isArray()->yes()) {
                    continue;
                }
                if (!$subType instanceof ArrayType) {
                    continue;
                }
                return $subType->getItemType();
            }
        }
        return null;
    }
}
