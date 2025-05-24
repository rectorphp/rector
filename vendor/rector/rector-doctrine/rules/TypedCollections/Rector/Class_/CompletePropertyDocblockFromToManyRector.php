<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Doctrine\CodeQuality\Enum\CollectionMapping;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Doctrine\NodeAnalyzer\TargetEntityResolver;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver;
use Rector\Doctrine\TypeAnalyzer\CollectionVarTagValueNodeResolver;
use Rector\Doctrine\TypedCollections\NodeAnalyzer\EntityLikeClassDetector;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\Class_\CompletePropertyDocblockFromToManyRector\CompletePropertyDocblockFromToManyRectorTest
 */
final class CompletePropertyDocblockFromToManyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private CollectionTypeFactory $collectionTypeFactory;
    /**
     * @readonly
     */
    private CollectionTypeResolver $collectionTypeResolver;
    /**
     * @readonly
     */
    private CollectionVarTagValueNodeResolver $collectionVarTagValueNodeResolver;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private EntityLikeClassDetector $entityLikeClassDetector;
    /**
     * @readonly
     */
    private AttributeFinder $attributeFinder;
    /**
     * @readonly
     */
    private TargetEntityResolver $targetEntityResolver;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(CollectionTypeFactory $collectionTypeFactory, CollectionTypeResolver $collectionTypeResolver, CollectionVarTagValueNodeResolver $collectionVarTagValueNodeResolver, PhpDocTypeChanger $phpDocTypeChanger, EntityLikeClassDetector $entityLikeClassDetector, AttributeFinder $attributeFinder, TargetEntityResolver $targetEntityResolver, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->collectionTypeFactory = $collectionTypeFactory;
        $this->collectionTypeResolver = $collectionTypeResolver;
        $this->collectionVarTagValueNodeResolver = $collectionVarTagValueNodeResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->entityLikeClassDetector = $entityLikeClassDetector;
        $this->attributeFinder = $attributeFinder;
        $this->targetEntityResolver = $targetEntityResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Improve Doctrine property @var collections type to make them useful both for PHPStan and PHPStorm', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Trainer::class, mappedBy="trainer")
     * @var Collection|Trainer[]
     */
    private $trainings = [];

    public function setTrainings($trainings)
    {
        $this->trainings = $trainings;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Trainer::class, mappedBy="trainer")
     * @var Collection<int, Trainer>
     */
    private $trainings = [];

    public function setTrainings($trainings)
    {
        $this->trainings = $trainings;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->entityLikeClassDetector->detect($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if ($this->refactorProperty($property)) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function refactorProperty(Property $property) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($phpDocInfo->hasByAnnotationClasses(CollectionMapping::TO_MANY_CLASSES)) {
            return (bool) $this->refactorPropertyPhpDocInfo($property, $phpDocInfo);
        }
        return (bool) $this->refactorPropertyAttribute($property, $phpDocInfo);
    }
    private function refactorPropertyPhpDocInfo(Property $property, PhpDocInfo $phpDocInfo) : ?Property
    {
        $varTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if ($varTagValueNode instanceof VarTagValueNode) {
            $collectionObjectType = $this->collectionTypeResolver->resolveFromTypeNode($varTagValueNode->type, $property);
            if (!$collectionObjectType instanceof FullyQualifiedObjectType) {
                return null;
            }
            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType, $this->collectionTypeResolver->hasIndexBy($property), $property);
            $hasChanged = $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $newVarType);
        } else {
            $collectionObjectType = $this->collectionTypeResolver->resolveFromToManyProperty($property);
            if (!$collectionObjectType instanceof FullyQualifiedObjectType) {
                return null;
            }
            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType, $this->collectionTypeResolver->hasIndexBy($property), $property);
            $hasChanged = $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $newVarType);
        }
        if (!$hasChanged) {
            return null;
        }
        return $property;
    }
    private function refactorPropertyAttribute(Property $property, PhpDocInfo $phpDocInfo) : ?Property
    {
        $toManyAttribute = $this->attributeFinder->findAttributeByClasses($property, CollectionMapping::TO_MANY_CLASSES);
        if (!$toManyAttribute instanceof Attribute) {
            return null;
        }
        $targetEntityClassName = $this->targetEntityResolver->resolveFromAttribute($toManyAttribute);
        if ($targetEntityClassName === null) {
            return null;
        }
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($targetEntityClassName);
        $genericObjectType = $this->collectionTypeFactory->createType($fullyQualifiedObjectType, $this->collectionTypeResolver->hasIndexBy($property), $property);
        $hasChanged = $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $genericObjectType);
        if (!$hasChanged) {
            return null;
        }
        return $property;
    }
}
