<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\Class_;

use RectorPrefix202505\Doctrine\Common\Collections\ArrayCollection;
use RectorPrefix202505\Doctrine\Common\Collections\Collection;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\NodeManipulator\ClassInsertManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\DefaultNewArrayCollectionRector\DefaultNewArrayCollectionRectorTest
 */
final class DefaultNewArrayCollectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ClassInsertManipulator $classInsertManipulator;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ClassInsertManipulator $classInsertManipulator)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->classInsertManipulator = $classInsertManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add default new ArrayCollection() to collection typed properties', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @var Collection<int, string>
     */
    private $items;

    public function __construct()
    {
        // ...
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @var Collection<int, string>
     */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        // ...
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if ($node->isAbstract()) {
            return null;
        }
        if ($this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $collectionPropertyNames = $this->resolveCollectionPropertyNames($node);
        if ($collectionPropertyNames === []) {
            return null;
        }
        $constructorClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        $definedProperties = $this->resolveConstructorDefinedProperties($constructorClassMethod);
        $missingDefaultProperties = \array_diff($collectionPropertyNames, $definedProperties);
        if ($missingDefaultProperties === []) {
            return null;
        }
        $propertyDefaultAssigns = $this->createDefaultPropertyAssigns($missingDefaultProperties);
        if ($constructorClassMethod instanceof ClassMethod) {
            $constructorClassMethod->stmts = \array_merge((array) $constructorClassMethod->stmts, $propertyDefaultAssigns);
        } else {
            $constructClassMethod = new ClassMethod(MethodName::CONSTRUCT);
            $constructClassMethod->flags |= Modifiers::PUBLIC;
            $constructClassMethod->stmts = $propertyDefaultAssigns;
            $this->classInsertManipulator->addAsFirstMethod($node, $constructClassMethod);
        }
        return $node;
    }
    /**
     * @return string[]
     */
    private function resolveConstructorDefinedProperties(?ClassMethod $constructorClassMethod) : array
    {
        if (!$constructorClassMethod instanceof ClassMethod) {
            return [];
        }
        if ($constructorClassMethod->stmts === null) {
            return [];
        }
        $definedProperties = [];
        foreach ($constructorClassMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            $propertyName = $this->getName($assign->var->name);
            if ($propertyName === null) {
                continue;
            }
            $definedProperties[] = $propertyName;
        }
        return $definedProperties;
    }
    /**
     * @param string[] $missingDefaultProperties
     * @return Expression[]
     */
    private function createDefaultPropertyAssigns(array $missingDefaultProperties) : array
    {
        $propertyDefaultAssigns = [];
        foreach ($missingDefaultProperties as $missingDefaultProperty) {
            $propertyFetch = new PropertyFetch(new Variable('this'), $missingDefaultProperty);
            $missingDefaultPropertyAssign = new Assign($propertyFetch, new New_(new FullyQualified(ArrayCollection::class)));
            $propertyDefaultAssigns[] = new Expression($missingDefaultPropertyAssign);
        }
        return $propertyDefaultAssigns;
    }
    /**
     * @return string[]
     */
    private function resolveCollectionPropertyNames(Class_ $class) : array
    {
        $collectionPropertyNames = [];
        foreach ($class->getProperties() as $property) {
            $propertyType = $this->getType($property);
            if (!$propertyType instanceof ObjectType) {
                continue;
            }
            if ($propertyType->getClassName() !== Collection::class) {
                continue;
            }
            $collectionPropertyNames[] = $this->getName($property);
        }
        return $collectionPropertyNames;
    }
}
