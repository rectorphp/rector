<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\NodeManipulator\AssignManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Doctrine\NodeAnalyzer\TargetEntityResolver;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver;
use Rector\Doctrine\TypeAnalyzer\CollectionVarTagValueNodeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\ImproveDoctrineCollectionDocTypeInEntityRectorTest
 */
final class ImproveDoctrineCollectionDocTypeInEntityRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory
     */
    private $collectionTypeFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver
     */
    private $collectionTypeResolver;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\CollectionVarTagValueNodeResolver
     */
    private $collectionVarTagValueNodeResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
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
    public function __construct(CollectionTypeFactory $collectionTypeFactory, AssignManipulator $assignManipulator, CollectionTypeResolver $collectionTypeResolver, CollectionVarTagValueNodeResolver $collectionVarTagValueNodeResolver, PhpDocTypeChanger $phpDocTypeChanger, DoctrineDocBlockResolver $doctrineDocBlockResolver, ReflectionResolver $reflectionResolver, AttributeFinder $attributeFinder, TargetEntityResolver $targetEntityResolver)
    {
        $this->collectionTypeFactory = $collectionTypeFactory;
        $this->assignManipulator = $assignManipulator;
        $this->collectionTypeResolver = $collectionTypeResolver;
        $this->collectionVarTagValueNodeResolver = $collectionVarTagValueNodeResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->attributeFinder = $attributeFinder;
        $this->targetEntityResolver = $targetEntityResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Improve @var, @param and @return types for Doctrine collections to make them useful both for PHPStan and PHPStorm', [new CodeSample(<<<'CODE_SAMPLE'
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
     * @var Collection<int, Trainer>|Trainer[]
     */
    private $trainings = [];
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class, Class_::class];
    }
    /**
     * @param Property|Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }
        return $this->refactorClassMethod($node);
    }
    private function refactorProperty(Property $property) : ?Property
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($phpDocInfo->hasByAnnotationClass('Doctrine\\ORM\\Mapping\\OneToMany')) {
            return $this->refactorPropertyPhpDocInfo($property, $phpDocInfo);
        }
        $targetEntityExpr = $this->attributeFinder->findAttributeByClassArgByName($property, 'Doctrine\\ORM\\Mapping\\OneToMany', 'targetEntity');
        if (!$targetEntityExpr instanceof Expr) {
            return null;
        }
        return $this->refactorAttribute($targetEntityExpr, $phpDocInfo, $property);
    }
    private function refactorClassMethod(Class_ $class) : ?Class_
    {
        if (!$this->doctrineDocBlockResolver->isDoctrineEntityClass($class)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($class->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            $collectionObjectType = $this->resolveCollectionSetterAssignType($class, $classMethod);
            if (!$collectionObjectType instanceof Type) {
                continue;
            }
            if (\count($classMethod->params) !== 1) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $param = $classMethod->params[0];
            if ($param->type instanceof Node) {
                continue;
            }
            /** @var string $parameterName */
            $parameterName = $this->getName($param);
            $this->phpDocTypeChanger->changeParamType($classMethod, $phpDocInfo, $collectionObjectType, $param, $parameterName);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $class;
        }
        return null;
    }
    private function resolveCollectionSetterAssignType(Class_ $class, ClassMethod $classMethod) : ?Type
    {
        $propertyFetches = $this->assignManipulator->resolveAssignsToLocalPropertyFetches($classMethod);
        if (\count($propertyFetches) !== 1) {
            return null;
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetches[0]);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return null;
        }
        $propertyName = (string) $this->nodeNameResolver->getName($propertyFetches[0]);
        $property = $class->getProperty($propertyName);
        if (!$property instanceof Property) {
            return null;
        }
        $varTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
    }
    private function refactorPropertyPhpDocInfo(Property $property, PhpDocInfo $phpDocInfo) : ?Property
    {
        $varTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if ($varTagValueNode instanceof VarTagValueNode) {
            $collectionObjectType = $this->collectionTypeResolver->resolveFromTypeNode($varTagValueNode->type, $property);
            if (!$collectionObjectType instanceof FullyQualifiedObjectType) {
                return null;
            }
            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType);
            $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $newVarType);
        } else {
            $collectionObjectType = $this->collectionTypeResolver->resolveFromOneToManyProperty($property);
            if (!$collectionObjectType instanceof FullyQualifiedObjectType) {
                return null;
            }
            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType);
            $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $newVarType);
        }
        return $property;
    }
    private function refactorAttribute(Expr $expr, PhpDocInfo $phpDocInfo, Property $property) : ?Property
    {
        $phpDocVarTagValueNode = $phpDocInfo->getVarTagValueNode();
        $phpDocCollectionVarTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if ($phpDocVarTagValueNode instanceof VarTagValueNode && !$phpDocCollectionVarTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        $targetEntityClassName = $this->targetEntityResolver->resolveFromExpr($expr);
        if ($targetEntityClassName === null) {
            return null;
        }
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($targetEntityClassName);
        $newVarType = $this->collectionTypeFactory->createType($fullyQualifiedObjectType);
        $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $newVarType);
        return $property;
    }
}
