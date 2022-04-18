<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use RectorPrefix20220418\Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Doctrine\NodeAnalyzer\TargetEntityResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ToOneRelationPropertyTypeResolver
{
    /**
     * @var class-string[]
     */
    private const TO_ONE_ANNOTATION_CLASSES = ['Doctrine\\ORM\\Mapping\\ManyToOne', 'Doctrine\\ORM\\Mapping\\OneToOne'];
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\TargetEntityResolver
     */
    private $targetEntityResolver;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher $classAnnotationMatcher, \Rector\Doctrine\NodeAnalyzer\AttributeFinder $attributeFinder, \Rector\Doctrine\NodeAnalyzer\TargetEntityResolver $targetEntityResolver)
    {
        $this->typeFactory = $typeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->attributeFinder = $attributeFinder;
        $this->targetEntityResolver = $targetEntityResolver;
    }
    public function resolve(\PhpParser\Node\Stmt\Property $property) : ?\PHPStan\Type\Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses(self::TO_ONE_ANNOTATION_CLASSES);
        if ($doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return $this->resolveFromDocBlock($phpDocInfo, $property, $doctrineAnnotationTagValueNode);
        }
        $targetEntity = $this->attributeFinder->findAttributeByClassesArgByName($property, self::TO_ONE_ANNOTATION_CLASSES, 'targetEntity');
        if (!$targetEntity instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $targetEntityClass = $this->targetEntityResolver->resolveFromExpr($targetEntity);
        if ($targetEntityClass !== null) {
            $fullyQualifiedObjectType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($targetEntityClass);
            // @todo resolve nullable value
            return $this->resolveFromObjectType($fullyQualifiedObjectType, \false);
        }
        return null;
    }
    private function processToOneRelation(\PhpParser\Node\Stmt\Property $property, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $toOneDoctrineAnnotationTagValueNode, ?\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $joinDoctrineAnnotationTagValueNode) : \PHPStan\Type\Type
    {
        $targetEntity = $toOneDoctrineAnnotationTagValueNode->getValueWithoutQuotes('targetEntity');
        if (!\is_string($targetEntity)) {
            return new \PHPStan\Type\MixedType();
        }
        if (\substr_compare($targetEntity, '::class', -\strlen('::class')) === 0) {
            $targetEntity = \RectorPrefix20220418\Nette\Utils\Strings::before($targetEntity, '::class');
        }
        // resolve to FQN
        $tagFullyQualifiedName = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($targetEntity, $property);
        $fullyQualifiedObjectType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($tagFullyQualifiedName);
        $isNullable = $this->isNullableType($joinDoctrineAnnotationTagValueNode);
        return $this->resolveFromObjectType($fullyQualifiedObjectType, $isNullable);
    }
    private function shouldAddNullType(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : bool
    {
        $isNullableValue = $doctrineAnnotationTagValueNode->getValue('nullable');
        return $isNullableValue instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
    }
    private function resolveFromDocBlock(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\Property $property, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : \PHPStan\Type\Type
    {
        $joinDoctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass('Doctrine\\ORM\\Mapping\\JoinColumn');
        return $this->processToOneRelation($property, $doctrineAnnotationTagValueNode, $joinDoctrineAnnotationTagValueNode);
    }
    private function resolveFromObjectType(\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType, bool $isNullable) : \PHPStan\Type\Type
    {
        $types = [];
        $types[] = $fullyQualifiedObjectType;
        if ($isNullable) {
            $types[] = new \PHPStan\Type\NullType();
        }
        $propertyType = $this->typeFactory->createMixedPassedOrUnionType($types);
        // add default null if missing
        if (!\PHPStan\Type\TypeCombinator::containsNull($propertyType)) {
            $propertyType = \PHPStan\Type\TypeCombinator::addNull($propertyType);
        }
        return $propertyType;
    }
    private function isNullableType(?\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $joinDoctrineAnnotationTagValueNode) : bool
    {
        if (!$joinDoctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return \false;
        }
        return $this->shouldAddNullType($joinDoctrineAnnotationTagValueNode);
    }
}
