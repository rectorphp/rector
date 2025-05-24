<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Doctrine\NodeAnalyzer\MethodUniqueReturnedPropertyResolver;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver;
use Rector\Doctrine\TypedCollections\NodeAnalyzer\EntityLikeClassDetector;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 *  @see \Rector\Doctrine\Tests\TypedCollections\Rector\Class_\CompleteReturnDocblockFromToManyRector\CompleteReturnDocblockFromToManyRectorTest
 */
final class CompleteReturnDocblockFromToManyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private EntityLikeClassDetector $entityLikeClassDetector;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private CollectionTypeResolver $collectionTypeResolver;
    /**
     * @readonly
     */
    private CollectionTypeFactory $collectionTypeFactory;
    /**
     * @readonly
     */
    private MethodUniqueReturnedPropertyResolver $methodUniqueReturnedPropertyResolver;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, PhpDocInfoFactory $phpDocInfoFactory, EntityLikeClassDetector $entityLikeClassDetector, PhpDocTypeChanger $phpDocTypeChanger, CollectionTypeResolver $collectionTypeResolver, CollectionTypeFactory $collectionTypeFactory, MethodUniqueReturnedPropertyResolver $methodUniqueReturnedPropertyResolver)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->entityLikeClassDetector = $entityLikeClassDetector;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->collectionTypeResolver = $collectionTypeResolver;
        $this->collectionTypeFactory = $collectionTypeFactory;
        $this->methodUniqueReturnedPropertyResolver = $methodUniqueReturnedPropertyResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds @return PHPDoc type to Collection property getter by *ToMany annotation/attribute', [new CodeSample(<<<'CODE_SAMPLE'
use App\Entity\Training;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class Trainer
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class)
     */
    private $trainings;

    public function getTrainings()
    {
        return $this->trainings;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class Trainer
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class)
     */
    private $trainings;

    /**
     * @return \Doctrine\Common\Collections\Collection<int, \App\Entity\Training>
     */
    public function getTrainings()
    {
        return $this->trainings;
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
        $scope = ScopeFetcher::fetch($node);
        foreach ($node->getMethods() as $classMethod) {
            if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod, $scope)) {
                continue;
            }
            $property = $this->methodUniqueReturnedPropertyResolver->resolve($node, $classMethod);
            if (!$property instanceof Property) {
                continue;
            }
            $collectionObjectType = $this->collectionTypeResolver->resolveFromToManyProperty($property);
            if (!$collectionObjectType instanceof FullyQualifiedObjectType) {
                continue;
            }
            // update docblock with known collection type
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType, $this->collectionTypeResolver->hasIndexBy($property), $property);
            $this->phpDocTypeChanger->changeReturnType($classMethod, $phpDocInfo, $newVarType);
            $hasChanged = \true;
        }
        return $hasChanged ? $node : null;
    }
}
