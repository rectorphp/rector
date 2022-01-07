<?php

declare (strict_types=1);
namespace Rector\Symfony\TypeDeclaration;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class ReturnTypeDeclarationUpdater
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @param class-string $className
     */
    public function updateClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $className) : void
    {
        $this->updatePhpDoc($classMethod, $className);
        $this->updatePhp($classMethod, $className);
    }
    /**
     * @param class-string $className
     */
    private function updatePhpDoc(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $className) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $returnTagValue = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValue instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode) {
            return;
        }
        $returnStaticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($returnTagValue->type, $classMethod);
        if ($returnStaticType instanceof \PHPStan\Type\ArrayType || $returnStaticType instanceof \PHPStan\Type\UnionType) {
            $returnTagValue->type = new \Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode($className);
        }
    }
    /**
     * @param class-string $className
     */
    private function updatePhp(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $className) : void
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::SCALAR_TYPES)) {
            return;
        }
        $objectType = new \PHPStan\Type\ObjectType($className);
        // change return type
        if ($classMethod->returnType !== null) {
            $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($classMethod->returnType);
            if ($objectType->isSuperTypeOf($returnType)->yes()) {
                return;
            }
        }
        $classMethod->returnType = new \PhpParser\Node\Name\FullyQualified($className);
    }
}
