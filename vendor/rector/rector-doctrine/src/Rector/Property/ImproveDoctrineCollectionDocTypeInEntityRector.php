<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassLike;
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
 * @see \Rector\Doctrine\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\ImproveDoctrineCollectionDocTypeInEntityRectorTest
 */
final class ImproveDoctrineCollectionDocTypeInEntityRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory $collectionTypeFactory, \Rector\Core\NodeManipulator\AssignManipulator $assignManipulator, \Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver $collectionTypeResolver, \Rector\Doctrine\TypeAnalyzer\CollectionVarTagValueNodeResolver $collectionVarTagValueNodeResolver, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver $doctrineDocBlockResolver, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\Doctrine\NodeAnalyzer\AttributeFinder $attributeFinder, \Rector\Doctrine\NodeAnalyzer\TargetEntityResolver $targetEntityResolver)
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
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Improve @var, @param and @return types for Doctrine collections to make them useful both for PHPStan and PHPStorm', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param Property|ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\Property) {
            return $this->refactorProperty($node);
        }
        return $this->refactorClassMethod($node);
    }
    private function refactorProperty(\PhpParser\Node\Stmt\Property $property) : ?\PhpParser\Node\Stmt\Property
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($phpDocInfo->hasByAnnotationClass('Doctrine\\ORM\\Mapping\\OneToMany')) {
            return $this->refactorPropertyPhpDocInfo($property, $phpDocInfo);
        }
        $targetEntityExpr = $this->attributeFinder->findAttributeByClassArgByName($property, 'Doctrine\\ORM\\Mapping\\OneToMany', 'targetEntity');
        if (!$targetEntityExpr instanceof \PhpParser\Node\Expr) {
            return null;
        }
        return $this->refactorAttribute($targetEntityExpr, $phpDocInfo, $property);
    }
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (!$this->doctrineDocBlockResolver->isInDoctrineEntityClass($classMethod)) {
            return null;
        }
        if (!$classMethod->isPublic()) {
            return null;
        }
        $collectionObjectType = $this->resolveCollectionSetterAssignType($classMethod);
        if (!$collectionObjectType instanceof \PHPStan\Type\Type) {
            return null;
        }
        if (\count($classMethod->params) !== 1) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $param = $classMethod->params[0];
        if ($param->type instanceof \PhpParser\Node) {
            return null;
        }
        /** @var string $parameterName */
        $parameterName = $this->getName($param);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $collectionObjectType, $param, $parameterName);
        return $classMethod;
    }
    private function resolveCollectionSetterAssignType(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PHPStan\Type\Type
    {
        $propertyFetches = $this->assignManipulator->resolveAssignsToLocalPropertyFetches($classMethod);
        if (\count($propertyFetches) !== 1) {
            return null;
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetches[0]);
        if (!$phpPropertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
            return null;
        }
        $classLike = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $propertyName = (string) $this->nodeNameResolver->getName($propertyFetches[0]);
        $property = $classLike->getProperty($propertyName);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return null;
        }
        $varTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if (!$varTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
    }
    private function refactorPropertyPhpDocInfo(\PhpParser\Node\Stmt\Property $property, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : ?\PhpParser\Node\Stmt\Property
    {
        $varTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if ($varTagValueNode !== null) {
            $collectionObjectType = $this->collectionTypeResolver->resolveFromTypeNode($varTagValueNode->type, $property);
            if (!$collectionObjectType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
                return null;
            }
            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType);
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $newVarType);
        } else {
            $collectionObjectType = $this->collectionTypeResolver->resolveFromOneToManyProperty($property);
            if (!$collectionObjectType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
                return null;
            }
            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType);
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $newVarType);
        }
        return $property;
    }
    private function refactorAttribute(\PhpParser\Node\Expr $expr, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\Property $property) : ?\PhpParser\Node\Stmt\Property
    {
        $phpDocVarTagValueNode = $phpDocInfo->getVarTagValueNode();
        $phpDocCollectionVarTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if ($phpDocVarTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode && !$phpDocCollectionVarTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            return null;
        }
        $targetEntityClassName = $this->targetEntityResolver->resolveFromExpr($expr);
        if ($targetEntityClassName === null) {
            return null;
        }
        $fullyQualifiedObjectType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($targetEntityClassName);
        $newVarType = $this->collectionTypeFactory->createType($fullyQualifiedObjectType);
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $newVarType);
        return $property;
    }
}
