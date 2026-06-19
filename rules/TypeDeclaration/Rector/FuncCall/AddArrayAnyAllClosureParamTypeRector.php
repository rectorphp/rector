<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
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
 * @see \Rector\Tests\TypeDeclaration\Rector\FuncCall\AddArrayAnyAllClosureParamTypeRector\AddArrayAnyAllClosureParamTypeRectorTest
 */
final class AddArrayAnyAllClosureParamTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @var string[]
     */
    private const FUNCTION_NAMES = ['array_any', 'array_all'];
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add a type to an untyped array_any()/array_all() closure param, based on the array item type', [new CodeSample(<<<'CODE_SAMPLE'
/** @var string[] $items */
array_any($items, fn ($item): bool => $item !== '');
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
        // only fill in a param that has no type yet
        if ($valueParam->type instanceof Node) {
            return null;
        }
        $itemType = $this->resolveArrayItemType($this->getType($arrayArg->value));
        if (!$itemType instanceof Type) {
            return null;
        }
        if ($itemType instanceof MixedType) {
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
