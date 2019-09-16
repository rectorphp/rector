<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\DeadCode\Doctrine\DoctrineEntityManipulator;
use Rector\DeadCode\UnusedNodeResolver\ClassUnusedPrivateClassMethodResolver;
use Rector\Doctrine\ValueObject\DoctrineClass;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\RemoveUnusedDoctrineEntityMethodAndPropertyRectorTest
 */
final class RemoveUnusedDoctrineEntityMethodAndPropertyRector extends AbstractRector
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var Assign[]
     */
    private $collectionByPropertyName = [];

    /**
     * @var ClassUnusedPrivateClassMethodResolver
     */
    private $classUnusedPrivateClassMethodResolver;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var DoctrineEntityManipulator
     */
    private $doctrineEntityManipulator;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(
        ParsedNodesByType $parsedNodesByType,
        ClassUnusedPrivateClassMethodResolver $classUnusedPrivateClassMethodResolver,
        ClassManipulator $classManipulator,
        DocBlockManipulator $docBlockManipulator,
        DoctrineEntityManipulator $doctrineEntityManipulator
    ) {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->classUnusedPrivateClassMethodResolver = $classUnusedPrivateClassMethodResolver;
        $this->classManipulator = $classManipulator;
        $this->doctrineEntityManipulator = $doctrineEntityManipulator;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes unused methods and properties from Doctrine entity classes', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UserEntity
{
    /**
     * @ORM\Column
     */
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UserEntity
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->doctrineEntityManipulator->isNonAbstractDoctrineEntityClass($node)) {
            return null;
        }

        $unusedMethodNames = $this->classUnusedPrivateClassMethodResolver->getClassUnusedMethodNames($node);
        if ($unusedMethodNames !== []) {
            $node = $this->removeClassMethodsByNames($node, $unusedMethodNames);
        }

        $unusedPropertyNames = $this->resolveUnusedPrivatePropertyNames($node);
        if ($unusedPropertyNames !== []) {
            $node = $this->removeClassPrivatePropertiesByNames($node, $unusedPropertyNames);
        }

        return $node;
    }

    /**
     * Remove unused methods immediately, so we can then remove unused properties.
     * @param string[] $unusedMethodNames
     */
    private function removeClassMethodsByNames(Class_ $class, array $unusedMethodNames): Class_
    {
        foreach ($class->getMethods() as $classMethod) {
            if (! $this->isNames($classMethod, $unusedMethodNames)) {
                continue;
            }

            $this->removeNodeFromStatements($class, $classMethod);
        }

        return $class;
    }

    /**
     * @return string[]
     */
    private function resolveUnusedPrivatePropertyNames(Class_ $class): array
    {
        $privatePropertyNames = $this->classManipulator->getPrivatePropertyNames($class);

        // get list of fetched properties
        $usedPropertyNames = $this->resolveClassUsedPropertyFetchNames($class);

        return array_diff($privatePropertyNames, $usedPropertyNames);
    }

    /**
     * @param string[] $unusedPropertyNames
     */
    private function removeClassPrivatePropertiesByNames(Class_ $class, array $unusedPropertyNames): Class_
    {
        foreach ($class->getProperties() as $property) {
            if (! $this->isNames($property, $unusedPropertyNames)) {
                continue;
            }

            $this->removeNodeFromStatements($class, $property);

            // remove "$this->someProperty = new ArrayCollection()"
            $propertyName = $this->getName($property);
            if (isset($this->collectionByPropertyName[$propertyName])) {
                $this->removeNode($this->collectionByPropertyName[$propertyName]);
            }

            $this->removeInversedByOrMappedByOnRelatedProperty($property);
        }

        return $class;
    }

    private function getOtherRelationProperty(Property $property): ?Property
    {
        $targetEntity = $this->docBlockManipulator->getDoctrineFqnTargetEntity($property);
        if ($targetEntity === null) {
            return null;
        }

        $otherProperty = $this->doctrineEntityManipulator->resolveOtherProperty($property);
        if ($otherProperty === null) {
            return null;
        }

        // get the class property and remove "mappedBy/inversedBy" from annotation
        $relatedEntityClass = $this->parsedNodesByType->findClass($targetEntity);
        if (! $relatedEntityClass instanceof Class_) {
            return null;
        }

        foreach ($relatedEntityClass->getProperties() as $relatedEntityClassStmt) {
            if (! $this->isName($relatedEntityClassStmt, $otherProperty)) {
                continue;
            }

            return $relatedEntityClassStmt;
        }

        return null;
    }

    private function removeInversedByOrMappedByOnRelatedProperty(Property $property): void
    {
        $otherRelationProperty = $this->getOtherRelationProperty($property);
        if ($otherRelationProperty === null) {
            return;
        }

        $this->doctrineEntityManipulator->removeMappedByOrInversedByFromProperty($otherRelationProperty);
    }

    private function isPropertyFetchAssignOfArrayCollection(PropertyFetch $propertyFetch): bool
    {
        $parentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Assign) {
            return false;
        }

        if (! $parentNode->expr instanceof New_) {
            return false;
        }

        /** @var New_ $new */
        $new = $parentNode->expr;

        return $this->isName($new->class, DoctrineClass::ARRAY_COLLECTION);
    }

    /**
     * @return string[]
     */
    private function resolveClassUsedPropertyFetchNames(Class_ $class): array
    {
        $usedPropertyNames = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (&$usedPropertyNames) {
            if (! $node instanceof PropertyFetch) {
                return null;
            }

            if (! $this->isName($node->var, 'this')) {
                return null;
            }

            /** @var string $propertyName */
            $propertyName = $this->getName($node->name);

            // skip collection initialization, e.g. "$this->someProperty = new ArrayCollection();"
            if ($this->isPropertyFetchAssignOfArrayCollection($node)) {
                /** @var Assign $parentNode */
                $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
                $this->collectionByPropertyName[$propertyName] = $parentNode;
                return null;
            }

            $usedPropertyNames[] = $propertyName;

            return null;
        });

        return $usedPropertyNames;
    }
}
