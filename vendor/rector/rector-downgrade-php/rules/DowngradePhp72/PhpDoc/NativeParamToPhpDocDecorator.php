<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\PhpDoc;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class NativeParamToPhpDocDecorator
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver, StaticTypeMapper $staticTypeMapper, PhpDocTypeChanger $phpDocTypeChanger, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->valueResolver = $valueResolver;
    }
    public function decorate(ClassMethod $classMethod, Param $param) : void
    {
        if (!$param->type instanceof Node) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $paramName = $this->nodeNameResolver->getName($param);
        $mappedCurrentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $correctedNullableParamType = $this->correctNullableType($param, $mappedCurrentParamType);
        $this->phpDocTypeChanger->changeParamType($classMethod, $phpDocInfo, $correctedNullableParamType, $param, $paramName);
    }
    private function isParamNullable(Param $param) : bool
    {
        if (!$param->default instanceof Expr) {
            return \false;
        }
        return $this->valueResolver->isNull($param->default);
    }
    /**
     * @return \PHPStan\Type\UnionType|\PHPStan\Type\Type
     */
    private function correctNullableType(Param $param, Type $paramType)
    {
        if (!$this->isParamNullable($param)) {
            return $paramType;
        }
        if (TypeCombinator::containsNull($paramType)) {
            return $paramType;
        }
        // add default null type
        return TypeCombinator::addNull($paramType);
    }
}
