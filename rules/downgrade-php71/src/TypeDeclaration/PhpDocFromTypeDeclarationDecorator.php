<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\TypeDeclaration;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class PhpDocFromTypeDeclarationDecorator
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(
        StaticTypeMapper $staticTypeMapper,
        PhpDocInfoFactory $phpDocInfoFactory,
        NodeNameResolver $nodeNameResolver,
        PhpDocTypeChanger $phpDocTypeChanger
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function decorateReturn(FunctionLike $functionLike): void
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
     * @param ClassMethod|Function_ $functionLike
     * @param array<class-string<Type>> $excludedTypes
     */
    public function decorateParam(Param $param, FunctionLike $functionLike, array $excludedTypes = [])
    {
        if ($param->type === null) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        foreach ($excludedTypes as $excludedType) {
            if (is_a($type, $excludedType, true)) {
                return;
            }
        }

        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     * @param class-string<Node|Type> $requireTypeNode
     */
    public function decorateParamWithSpecificType(Param $param, FunctionLike $functionLike, string $requireTypeNode)
    {
        if ($param->type === null) {
            return;
        }

        if (! $this->isTypeMatch($param->type, $requireTypeNode)) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     * @param class-string<Node|Type> $requireTypeNode
     */
    public function decorateReturnWithSpecificType(FunctionLike $functionLike, string $requireTypeNode): void
    {
        if ($functionLike->returnType === null) {
            return;
        }

        if (! $this->isTypeMatch($functionLike->returnType, $requireTypeNode)) {
            return;
        }

        $this->decorateReturn($functionLike);
    }

    /**
     * @param class-string<Node|Type> $requireTypeNodeClass
     */
    private function isTypeMatch(Node $typeNode, string $requireTypeNodeClass): bool
    {
        if (is_a($requireTypeNodeClass, Node::class, true)) {
            if (! is_a($typeNode, $requireTypeNodeClass, true)) {
                return false;
            }
        }

        if (is_a($requireTypeNodeClass, Type::class, true)) {
            $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($typeNode);
            if (! is_a($returnType, $requireTypeNodeClass, true)) {
                return false;
            }
        }

        return true;
    }

    private function moveParamTypeToParamDoc($functionLike, Param $param, Type $type): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramName = $this->nodeNameResolver->getName($param);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);

        $param->type = null;
    }
}
