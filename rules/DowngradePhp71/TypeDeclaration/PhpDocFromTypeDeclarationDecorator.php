<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\TypeDeclaration;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class PhpDocFromTypeDeclarationDecorator
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private NodeNameResolver $nodeNameResolver,
        private PhpDocTypeChanger $phpDocTypeChanger,
        private TypeUnwrapper $typeUnwrapper
    ) {
    }

    public function decorate(ClassMethod | Function_ | Closure $functionLike): void
    {
        if ($functionLike->returnType === null) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $type);

        $functionLike->returnType = null;
    }

    /**
     * @param array<class-string<Type>> $requiredTypes
     */
    public function decorateParam(
        Param $param,
        ClassMethod | Function_ | Closure $functionLike,
        array $requiredTypes
    ): void {
        if ($param->type === null) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);

        if (! $this->isMatchingType($type, $requiredTypes)) {
            return;
        }

        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }

    public function decorateParamWithSpecificType(
        Param $param,
        ClassMethod | Function_ | Closure $functionLike,
        Type $requireType
    ): void {
        if ($param->type === null) {
            return;
        }

        if (! $this->isTypeMatchOrSubType($param->type, $requireType)) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }

    /**
     * @return bool True if node was changed
     */
    public function decorateReturnWithSpecificType(
        ClassMethod | Function_ | Closure $functionLike,
        Type $requireType
    ): bool {
        if ($functionLike->returnType === null) {
            return false;
        }

        if (! $this->isTypeMatchOrSubType($functionLike->returnType, $requireType)) {
            return false;
        }

        $this->decorate($functionLike);
        return true;
    }

    private function isTypeMatchOrSubType(Node $typeNode, Type $requireType): bool
    {
        $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($typeNode);

        // cover nullable union types
        if ($returnType instanceof UnionType) {
            $returnType = $this->typeUnwrapper->unwrapNullableType($returnType);
        }
        return is_a($returnType, $requireType::class, true);
    }

    private function moveParamTypeToParamDoc(
        ClassMethod | Function_ | Closure $functionLike,
        Param $param,
        Type $type
    ): void {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramName = $this->nodeNameResolver->getName($param);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);

        $param->type = null;
    }

    /**
     * @param array<class-string<Type>> $requiredTypes
     */
    private function isMatchingType(Type $type, array $requiredTypes): bool
    {
        foreach ($requiredTypes as $requiredType) {
            if (is_a($type, $requiredType, true)) {
                return true;
            }
        }

        return false;
    }
}
