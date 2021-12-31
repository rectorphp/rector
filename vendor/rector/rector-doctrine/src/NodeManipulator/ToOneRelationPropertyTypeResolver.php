<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use RectorPrefix20211231\Nette\Utils\Strings;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ToOneRelationPropertyTypeResolver
{
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
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher $classAnnotationMatcher)
    {
        $this->typeFactory = $typeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
    }
    public function resolve(\PhpParser\Node\Stmt\Property $property) : ?\PHPStan\Type\Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $toOneRelationTagValueNode = $phpDocInfo->getByAnnotationClasses(['Doctrine\\ORM\\Mapping\\ManyToOne', 'Doctrine\\ORM\\Mapping\\OneToOne']);
        if ($toOneRelationTagValueNode !== null) {
            $joinDoctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass('Doctrine\\ORM\\Mapping\\JoinColumn');
            return $this->processToOneRelation($property, $toOneRelationTagValueNode, $joinDoctrineAnnotationTagValueNode);
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
            $targetEntity = \RectorPrefix20211231\Nette\Utils\Strings::before($targetEntity, '::class');
        }
        // resolve to FQN
        $tagFullyQualifiedName = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($targetEntity, $property);
        $types = [];
        $types[] = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($tagFullyQualifiedName);
        if ($joinDoctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode && $this->shouldAddNullType($joinDoctrineAnnotationTagValueNode)) {
            $types[] = new \PHPStan\Type\NullType();
        }
        $propertyType = $this->typeFactory->createMixedPassedOrUnionType($types);
        // add default null if missing
        if (!\PHPStan\Type\TypeCombinator::containsNull($propertyType)) {
            $propertyType = \PHPStan\Type\TypeCombinator::addNull($propertyType);
        }
        return $propertyType;
    }
    private function shouldAddNullType(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : bool
    {
        $isNullableValue = $doctrineAnnotationTagValueNode->getValue('nullable');
        return $isNullableValue instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
    }
}
