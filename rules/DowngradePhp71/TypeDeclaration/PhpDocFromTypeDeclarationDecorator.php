<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\TypeDeclaration;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
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
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper $typeUnwrapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->typeUnwrapper = $typeUnwrapper;
    }
    /**
     * @param ClassMethod|Function_|Closure $functionLike
     * @return bool True if node was changed
     */
    public function decorate(\PhpParser\Node\FunctionLike $functionLike) : bool
    {
        if ($functionLike->returnType === null) {
            return \false;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $type);
        $functionLike->returnType = null;
        return \true;
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     * @param array<class-string<Type>> $requiredTypes
     */
    public function decorateParam(\PhpParser\Node\Param $param, \PhpParser\Node\FunctionLike $functionLike, array $requiredTypes) : void
    {
        if ($param->type === null) {
            return;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        if (!$this->isMatchingType($type, $requiredTypes)) {
            return;
        }
        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function decorateParamWithSpecificType(\PhpParser\Node\Param $param, \PhpParser\Node\FunctionLike $functionLike, \PHPStan\Type\Type $requireType) : void
    {
        if ($param->type === null) {
            return;
        }
        if (!$this->isTypeMatchOrSubType($param->type, $requireType)) {
            return;
        }
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $this->moveParamTypeToParamDoc($functionLike, $param, $type);
    }
    /**
     * @param ClassMethod|Function_|Closure $functionLike
     * @return bool True if node was changed
     */
    public function decorateReturnWithSpecificType(\PhpParser\Node\FunctionLike $functionLike, \PHPStan\Type\Type $requireType) : bool
    {
        if ($functionLike->returnType === null) {
            return \false;
        }
        if (!$this->isTypeMatchOrSubType($functionLike->returnType, $requireType)) {
            return \false;
        }
        return $this->decorate($functionLike);
    }
    private function isTypeMatchOrSubType(\PhpParser\Node $typeNode, \PHPStan\Type\Type $requireType) : bool
    {
        $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($typeNode);
        // cover nullable union types
        if ($returnType instanceof \PHPStan\Type\UnionType) {
            $returnType = $this->typeUnwrapper->unwrapNullableType($returnType);
        }
        return \is_a($returnType, \get_class($requireType), \true);
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function moveParamTypeToParamDoc(\PhpParser\Node\FunctionLike $functionLike, \PhpParser\Node\Param $param, \PHPStan\Type\Type $type) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramName = $this->nodeNameResolver->getName($param);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);
        $param->type = null;
    }
    /**
     * @param array<class-string<Type>> $requiredTypes
     */
    private function isMatchingType(\PHPStan\Type\Type $type, array $requiredTypes) : bool
    {
        foreach ($requiredTypes as $requiredType) {
            if (\is_a($type, $requiredType, \true)) {
                return \true;
            }
        }
        return \false;
    }
}
