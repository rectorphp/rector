<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FuncCall\NarrowArrayAnyAllNullableParamTypeRector\NarrowArrayAnyAllNullableParamTypeRectorTest
 */
final class NarrowArrayAnyAllNullableParamTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @var string[]
     */
    private const FUNCTION_NAMES = ['array_any', 'array_all', 'array_find', 'array_find_key'];
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Narrow an already nullable array_any()/array_all()/array_find()/array_find_key() closure param to the non-nullable array item type', [new CodeSample(<<<'CODE_SAMPLE'
/** @var string[] $items */
array_any($items, fn (?string $item): bool => $item !== '');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/** @var string[] $items */
array_any($items, fn (string $item): bool => $item !== '');
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
        if (!$this->isNames($node, self::FUNCTION_NAMES)) {
            return null;
        }
        $arrayArg = $node->getArg('array', 0);
        $callbackArg = $node->getArg('callback', 1);
        if (!$arrayArg instanceof Arg || !$callbackArg instanceof Arg) {
            return null;
        }
        $callbackExpr = $callbackArg->value;
        if (!$callbackExpr instanceof ArrowFunction && !$callbackExpr instanceof Closure) {
            return null;
        }
        $valueParam = $callbackExpr->getParams()[0] ?? null;
        if (!$valueParam instanceof Param) {
            return null;
        }
        // only narrow a param that currently allows null
        if (!$valueParam->type instanceof NullableType) {
            return null;
        }
        $itemType = $this->resolveArrayItemType($this->getType($arrayArg->value));
        if (!$itemType instanceof Type) {
            return null;
        }
        // nothing to narrow when the item type is itself nullable or unknown
        if ($itemType instanceof MixedType || !$itemType->isNull()->no()) {
            return null;
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($itemType, TypeKind::PARAM);
        if (!$paramTypeNode instanceof Node) {
            return null;
        }
        $valueParam->type = $paramTypeNode;
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ARRAY_ANY;
    }
    private function resolveArrayItemType(Type $arrayType): ?Type
    {
        if ($arrayType instanceof ConstantArrayType || $arrayType instanceof ArrayType) {
            return $arrayType->getItemType();
        }
        if ($arrayType instanceof IntersectionType) {
            foreach ($arrayType->getTypes() as $subType) {
                if ($subType instanceof AccessoryArrayListType) {
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
