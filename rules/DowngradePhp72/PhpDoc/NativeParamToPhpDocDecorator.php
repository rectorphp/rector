<?php

declare(strict_types=1);

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
    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory,
        private NodeNameResolver $nodeNameResolver,
        private StaticTypeMapper $staticTypeMapper,
        private PhpDocTypeChanger $phpDocTypeChanger,
        private ValueResolver $valueResolver
    ) {
    }

    public function decorate(ClassMethod $classMethod, Param $param): void
    {
        if ($param->type === null) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $paramName = $this->nodeNameResolver->getName($param);
        $mappedCurrentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);

        // add default null type
        if ($param->default !== null && $this->valueResolver->isNull($param->default) && ! TypeCombinator::containsNull(
            $mappedCurrentParamType
        )) {
            $mappedCurrentParamType = new UnionType([$mappedCurrentParamType, new NullType()]);
        }

        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $mappedCurrentParamType, $param, $paramName);
    }
}
