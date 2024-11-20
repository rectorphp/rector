<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Doctrine\NodeAnalyzer\MethodUniqueReturnedPropertyResolver;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 *  @see \Rector\Doctrine\Tests\CodeQuality\Rector\Class_\AddReturnDocBlockToCollectionPropertyGetterByToManyAnnotationRector\AddReturnDocBlockToCollectionPropertyGetterByToManyAnnotationRectorTest
 */
final class AddReturnDocBlockToCollectionPropertyGetterByToManyAnnotationRector extends AbstractRector
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
    private DoctrineDocBlockResolver $doctrineDocBlockResolver;
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
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, PhpDocInfoFactory $phpDocInfoFactory, DoctrineDocBlockResolver $doctrineDocBlockResolver, PhpDocTypeChanger $phpDocTypeChanger, CollectionTypeResolver $collectionTypeResolver, CollectionTypeFactory $collectionTypeFactory, MethodUniqueReturnedPropertyResolver $methodUniqueReturnedPropertyResolver)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->collectionTypeResolver = $collectionTypeResolver;
        $this->collectionTypeFactory = $collectionTypeFactory;
        $this->methodUniqueReturnedPropertyResolver = $methodUniqueReturnedPropertyResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds @return PHPDoc type to Collection property getter by *ToMany annotation/attribute', [new CodeSample(<<<'CODE_SAMPLE'
use App\Entity\Training;

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
        if (!$this->doctrineDocBlockResolver->isDoctrineEntityClass($node)) {
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
            $newVarType = $this->collectionTypeFactory->createType($collectionObjectType);
            $this->phpDocTypeChanger->changeReturnType($classMethod, $phpDocInfo, $newVarType);
            $hasChanged = \true;
        }
        return $hasChanged ? $node : null;
    }
}
