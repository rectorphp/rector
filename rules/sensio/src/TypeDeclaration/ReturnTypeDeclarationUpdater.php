<?php

declare(strict_types=1);

namespace Rector\Sensio\TypeDeclaration;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\UnionType;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareFullyQualifiedIdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoManipulator;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class ReturnTypeDeclarationUpdater
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PhpDocInfoManipulator
     */
    private $phpDocInfoManipulator;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        PhpDocInfoManipulator $phpDocInfoManipulator,
        PhpVersionProvider $phpVersionProvider,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoManipulator = $phpDocInfoManipulator;
    }

    public function updateClassMethod(ClassMethod $classMethod, string $className): void
    {
        $this->updatePhpDoc($classMethod, $className);
        $this->updatePhp($classMethod, $className);
    }

    private function updatePhpDoc(ClassMethod $classMethod, string $className): void
    {
        /** @var ReturnTagValueNode|null $returnTagValueNode */
        $returnTagValueNode = $this->phpDocInfoManipulator->getPhpDocTagValueNode(
            $classMethod,
            ReturnTagValueNode::class
        );
        if ($returnTagValueNode === null) {
            return;
        }

        $returnStaticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $returnTagValueNode->type,
            $classMethod
        );

        if ($returnStaticType instanceof ArrayType || $returnStaticType instanceof UnionType) {
            $returnTagValueNode->type = new AttributeAwareFullyQualifiedIdentifierTypeNode($className);
        }
    }

    private function updatePhp(ClassMethod $classMethod, string $className): void
    {
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return;
        }

        // change return type
        if ($classMethod->returnType !== null) {
            $returnTypeName = $this->nodeNameResolver->getName($classMethod->returnType);
            if ($returnTypeName !== null && is_a($returnTypeName, $className, true)) {
                return;
            }
        }

        $classMethod->returnType = new FullyQualified($className);
    }
}
