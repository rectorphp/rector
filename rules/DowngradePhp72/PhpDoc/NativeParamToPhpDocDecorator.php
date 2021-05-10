<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\PhpDoc;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\NullType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class NativeParamToPhpDocDecorator
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->valueResolver = $valueResolver;
    }
    public function decorate(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param) : void
    {
        if ($param->type === null) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $paramName = $this->nodeNameResolver->getName($param);
        $mappedCurrentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        // add default null type
        if ($param->default !== null && $this->valueResolver->isNull($param->default) && !\PHPStan\Type\TypeCombinator::containsNull($mappedCurrentParamType)) {
            $mappedCurrentParamType = new \PHPStan\Type\UnionType([$mappedCurrentParamType, new \PHPStan\Type\NullType()]);
        }
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $mappedCurrentParamType, $param, $paramName);
    }
}
