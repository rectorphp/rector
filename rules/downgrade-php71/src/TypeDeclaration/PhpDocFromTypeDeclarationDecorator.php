<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\TypeDeclaration;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
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
     * @param array<class-string<Type>> $allowedTypes
     */
    public function decorateParam(Param $param, FunctionLike $functionLike, array $allowedTypes = [])
    {
        if ($param->type === null) {
            return ;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        foreach ($allowedTypes as $allowedType) {
            if (is_a($type, $allowedType, true)) {
                return;
            }
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramName = $this->nodeNameResolver->getName($param);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);

        $param->type = null;
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
}
