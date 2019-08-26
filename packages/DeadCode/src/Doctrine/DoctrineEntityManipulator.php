<?php declare(strict_types=1);

namespace Rector\DeadCode\Doctrine;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\NamespaceAnalyzer;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class DoctrineEntityManipulator
{
    /**
     * @var string
     */
    private const TARGET_ENTITY_PATTERN = '#targetEntity="(?<class>.*?)"#';

    /**
     * @var string
     */
    private const TARGET_PROPERTY_PATTERN = '#(inversedBy|mappedBy)="(?<property>.*?)"#';

    /**
     * @var string[]
     */
    private const RELATION_ANNOTATIONS = [
        OneToMany::class,
        self::MANY_TO_ONE_ANNOTATION,
        OneToOne::class,
        ManyToMany::class,
    ];

    /**
     * @var string
     */
    private const MANY_TO_ONE_ANNOTATION = ManyToOne::class;

    /**
     * @var string
     */
    private const MAPPED_OR_INVERSED_BY_PATTERN = '#(,\s+)?(inversedBy|mappedBy)="(?<property>.*?)"#';

    /**
     * @var string
     */
    private const JOIN_COLUMN_ANNOTATION = JoinColumn::class;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        NamespaceAnalyzer $namespaceAnalyzer,
        NameResolver $nameResolver
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
        $this->nameResolver = $nameResolver;
    }

    public function resolveTargetClass(Property $property): ?string
    {
        foreach (self::RELATION_ANNOTATIONS as $relationAnnotation) {
            if (! $this->docBlockManipulator->hasTag($property, $relationAnnotation)) {
                continue;
            }

            $relationTag = $this->docBlockManipulator->getTagByName($property, $relationAnnotation);
            if (! $relationTag->value instanceof GenericTagValueNode) {
                throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
            }

            $match = Strings::match($relationTag->value->value, self::TARGET_ENTITY_PATTERN);
            if (! isset($match['class'])) {
                return null;
            }

            $class = $match['class'];

            // fqnize possibly shorten class
            if (Strings::contains($class, '\\')) {
                return $class;
            }

            if (! class_exists($class)) {
                return $this->namespaceAnalyzer->resolveTypeToFullyQualified($class, $property);
            }

            return $class;
        }

        return null;
    }

    public function resolveOtherProperty(Property $property): ?string
    {
        foreach (self::RELATION_ANNOTATIONS as $relationAnnotation) {
            if (! $this->docBlockManipulator->hasTag($property, $relationAnnotation)) {
                continue;
            }

            $relationTag = $this->docBlockManipulator->getTagByName($property, $relationAnnotation);
            if (! $relationTag->value instanceof GenericTagValueNode) {
                throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
            }

            $match = Strings::match($relationTag->value->value, self::TARGET_PROPERTY_PATTERN);

            return $match['property'] ?? null;
        }

        return null;
    }

    public function isStandaloneDoctrineEntityClass(Class_ $class): bool
    {
        if ($class->isAnonymous()) {
            return false;
        }

        if ($class->isAbstract()) {
            return false;
        }

        // is parent entity
        if ($this->docBlockManipulator->hasTag($class, InheritanceType::class)) {
            return false;
        }

        return $this->docBlockManipulator->hasTag($class, Entity::class);
    }

    public function removeMappedByOrInversedByFromProperty(Property $property): void
    {
        $doc = $property->getDocComment();
        if ($doc === null) {
            return;
        }

        $originalDocText = $doc->getText();
        $clearedDocText = Strings::replace($originalDocText, self::MAPPED_OR_INVERSED_BY_PATTERN);

        // no change
        if ($originalDocText === $clearedDocText) {
            return;
        }

        $property->setDocComment(new Doc($clearedDocText));
    }

    public function isNullableRelation(Property $property): bool
    {
        if (! $this->docBlockManipulator->hasTag($property, self::JOIN_COLUMN_ANNOTATION)) {
            // @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/annotations-reference.html#joincolumn
            return true;
        }

        $joinColumnTag = $this->docBlockManipulator->getTagByName($property, self::JOIN_COLUMN_ANNOTATION);

        if ($joinColumnTag->value instanceof GenericTagValueNode) {
            return (bool) Strings::match($joinColumnTag->value->value, '#nullable=true#');
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function resolveRelationPropertyNames(Class_ $class): array
    {
        $manyToOnePropertyNames = [];

        foreach ($class->stmts as $stmt) {
            if (! $stmt instanceof Property) {
                continue;
            }

            if (! $this->isRelationProperty($stmt)) {
                continue;
            }

            $manyToOnePropertyNames[] = $this->nameResolver->getName($stmt);
        }

        return $manyToOnePropertyNames;
    }

    private function isRelationProperty(Node $node): bool
    {
        foreach (self::RELATION_ANNOTATIONS as $relationAnnotation) {
            if ($this->docBlockManipulator->hasTag($node, $relationAnnotation)) {
                return true;
            }
        }

        return false;
    }
}
