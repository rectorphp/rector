<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

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
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
/**
 * Shared logic for AddArrayAnyAllClosureParamTypeRector and NarrowArrayAnyAllNullableParamTypeRector.
 */
final class ArrayCallbackParamTypeResolver
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * Sets the first callback param type of an array_any()/array_all()/array_find()/array_find_key() call to the
     * array item type. With $narrowNullable, only a nullable param is narrowed; otherwise only an untyped param is
     * filled in.
     *
     * @param string[] $functionNames
     */
    public function refactorFirstParamType(FuncCall $funcCall, array $functionNames, bool $narrowNullable): ?Node
    {
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        if (!$this->nodeNameResolver->isNames($funcCall, $functionNames)) {
            return null;
        }
        $arrayArg = $funcCall->getArg('array', 0);
        $callbackArg = $funcCall->getArg('callback', 1);
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
        if ($narrowNullable) {
            // only narrow a param that currently allows null
            if (!$valueParam->type instanceof NullableType) {
                return null;
            }
        } elseif ($valueParam->type instanceof Node) {
            // only fill in a param that has no type yet
            return null;
        }
        $itemType = $this->resolveArrayItemType($this->nodeTypeResolver->getType($arrayArg->value));
        if (!$itemType instanceof Type) {
            return null;
        }
        if ($itemType instanceof MixedType) {
            return null;
        }
        // nothing to narrow when the item type is itself nullable or unknown
        if ($narrowNullable && !$itemType->isNull()->no()) {
            return null;
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($itemType, TypeKind::PARAM);
        if (!$paramTypeNode instanceof Node) {
            return null;
        }
        $valueParam->type = $paramTypeNode;
        return $funcCall;
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
