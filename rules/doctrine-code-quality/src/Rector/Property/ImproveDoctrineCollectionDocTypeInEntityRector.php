<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\Core\PhpParser\Node\Manipulator\AssignManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\DoctrineCodeQuality\PhpDoc\CollectionTypeFactory;
use Rector\DoctrineCodeQuality\PhpDoc\CollectionTypeResolver;
use Rector\DoctrineCodeQuality\PhpDoc\CollectionVarTagValueNodeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\ImproveDoctrineCollectionDocTypeInEntityRectorTest
 */
final class ImproveDoctrineCollectionDocTypeInEntityRector extends AbstractRector
{
    /**
     * @var CollectionTypeFactory
     */
    private $collectionTypeFactory;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    /**
     * @var CollectionTypeResolver
     */
    private $collectionTypeResolver;

    /**
     * @var CollectionVarTagValueNodeResolver
     */
    private $collectionVarTagValueNodeResolver;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(
        CollectionTypeFactory $collectionTypeFactory,
        AssignManipulator $assignManipulator,
        CollectionTypeResolver $collectionTypeResolver,
        CollectionVarTagValueNodeResolver $collectionVarTagValueNodeResolver,
        PhpDocTypeChanger $phpDocTypeChanger
    ) {
        $this->collectionTypeFactory = $collectionTypeFactory;
        $this->assignManipulator = $assignManipulator;
        $this->collectionTypeResolver = $collectionTypeResolver;
        $this->collectionVarTagValueNodeResolver = $collectionVarTagValueNodeResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Improve @var, @param and @return types for Doctrine collections to make them useful both for PHPStan and PHPStorm',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var Collection|Trainer[]
     */
    private $trainings = [];
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var Collection<int, Training>|Trainer[]
     */
    private $trainings = [];
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class, ClassMethod::class];
    }

    /**
     * @param Property|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }

        return $this->refactorClassMethod($node);
    }

    private function refactorProperty(Property $property): ?Property
    {
        if (! $this->hasNodeTagValueNode($property, OneToManyTagValueNode::class)) {
            return null;
        }

        // @todo make an own local property on enter node?
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $attributeAwareVarTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if ($attributeAwareVarTagValueNode !== null) {
            $collectionObjectType = $this->collectionTypeResolver->resolveFromTypeNode(
                $attributeAwareVarTagValueNode->type,
                $property
            );

            if ($collectionObjectType === null) {
                return null;
            }

            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType);
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $newVarType);
        } else {
            $collectionObjectType = $this->collectionTypeResolver->resolveFromOneToManyProperty($property);
            if ($collectionObjectType === null) {
                return null;
            }

            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType);
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $newVarType);
        }

        return $property;
    }

    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        if (! $this->isInDoctrineEntityClass($classMethod)) {
            return null;
        }

        if (! $classMethod->isPublic()) {
            return null;
        }

        $collectionObjectType = $this->resolveCollectionSetterAssignType($classMethod);
        if ($collectionObjectType === null) {
            return null;
        }

        if (count($classMethod->params) !== 1) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $param = $classMethod->params[0];
        $parameterName = $this->getName($param);

        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $collectionObjectType, $param, $parameterName);

        return $classMethod;
    }

    private function hasNodeTagValueNode(Property $property, string $tagValueNodeClass): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $phpDocInfo->hasByType($tagValueNodeClass);
    }

    private function resolveCollectionSetterAssignType(ClassMethod $classMethod): ?Type
    {
        $propertyFetches = $this->assignManipulator->resolveAssignsToLocalPropertyFetches($classMethod);
        if (count($propertyFetches) !== 1) {
            return null;
        }

        if (! $propertyFetches[0] instanceof PropertyFetch) {
            return null;
        }

        $property = $this->matchPropertyFetchToClassProperty($propertyFetches[0]);
        if ($property === null) {
            return null;
        }

        $varTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if ($varTagValueNode === null) {
            return null;
        }

        return $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
    }

    private function matchPropertyFetchToClassProperty(PropertyFetch $propertyFetch): ?Property
    {
        $propertyName = $this->getName($propertyFetch);
        if ($propertyName === null) {
            return null;
        }

        $classLike = $propertyFetch->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        return $classLike->getProperty($propertyName);
    }
}
