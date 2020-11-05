<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineCodeQuality\NodeAnalyzer\DoctrinePropertyAnalyzer;
use Rector\DoctrineCodeQuality\NodeManipulator\DoctrineItemDefaultValueManipulator;

/**
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\RemoveRedundantDefaultPropertyAnnotationValuesRectorTest
 */
final class RemoveRedundantDefaultPropertyAnnotationValuesRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ORPHAN_REMOVAL = 'orphanRemoval';

    /**
     * @var string
     */
    private const FETCH = 'fetch';

    /**
     * @var string
     */
    private const LAZY = 'LAZY';

    /**
     * @var DoctrinePropertyAnalyzer
     */
    private $doctrinePropertyAnalyzer;

    /**
     * @var DoctrineItemDefaultValueManipulator
     */
    private $doctrineItemDefaultValueManipulator;

    public function __construct(
        DoctrinePropertyAnalyzer $doctrinePropertyAnalyzer,
        DoctrineItemDefaultValueManipulator $doctrineItemDefaultValueManipulator
    ) {
        $this->doctrinePropertyAnalyzer = $doctrinePropertyAnalyzer;
        $this->doctrineItemDefaultValueManipulator = $doctrineItemDefaultValueManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes redundant default values from Doctrine ORM annotations on class property level',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training", unique=false)
     */
    private $training;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training")
     */
    private $training;
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
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->doctrineItemDefaultValueManipulator->resetHasModifiedAnnotation();
        if ($node instanceof Property) {
            $this->refactorPropertyAnnotations($node);
        }

        if (! $this->doctrineItemDefaultValueManipulator->hasModifiedAnnotation()) {
            return null;
        }

        return $node;
    }

    private function refactorPropertyAnnotations(Property $property): void
    {
        $this->refactorColumnAnnotation($property);
        $this->refactorGeneratedValueAnnotation($property);
        $this->refactorJoinColumnAnnotation($property);
        $this->refactorManyToManyAnnotation($property);
        $this->refactorManyToOneAnnotation($property);
        $this->refactorOneToManyAnnotation($property);
        $this->refactorOneToOneAnnotation($property);
    }

    private function refactorColumnAnnotation(Property $property): void
    {
        $columnTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineColumnTagValueNode($property);
        if ($columnTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($columnTagValueNode, 'nullable', false);
        $this->doctrineItemDefaultValueManipulator->remove($columnTagValueNode, 'unique', false);
        $this->doctrineItemDefaultValueManipulator->remove($columnTagValueNode, 'precision', 0);
        $this->doctrineItemDefaultValueManipulator->remove($columnTagValueNode, 'scale', 0);
    }

    private function refactorGeneratedValueAnnotation(Property $property): void
    {
        $generatedValueTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineGeneratedValueTagValueNode(
            $property
        );
        if ($generatedValueTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($generatedValueTagValueNode, 'strategy', 'AUTO');
    }

    private function refactorJoinColumnAnnotation(Property $property): void
    {
        $joinColumnTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineJoinColumnTagValueNode($property);
        if ($joinColumnTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($joinColumnTagValueNode, 'nullable', true);
        $this->doctrineItemDefaultValueManipulator->remove($joinColumnTagValueNode, 'referencedColumnName', 'id');
        $this->doctrineItemDefaultValueManipulator->remove($joinColumnTagValueNode, 'unique', false);
    }

    private function refactorManyToManyAnnotation(Property $property): void
    {
        $manyToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineManyToManyTagValueNode($property);
        if ($manyToManyTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($manyToManyTagValueNode, self::ORPHAN_REMOVAL, false);
        $this->doctrineItemDefaultValueManipulator->remove($manyToManyTagValueNode, self::FETCH, self::LAZY);
    }

    private function refactorManyToOneAnnotation(Property $property): void
    {
        $manyToOneTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineManyToOneTagValueNode($property);
        if ($manyToOneTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($manyToOneTagValueNode, self::FETCH, self::LAZY);
    }

    private function refactorOneToManyAnnotation(Property $property): void
    {
        $oneToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineOneToManyTagValueNode($property);
        if ($oneToManyTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($oneToManyTagValueNode, self::ORPHAN_REMOVAL, false);
        $this->doctrineItemDefaultValueManipulator->remove($oneToManyTagValueNode, self::FETCH, self::LAZY);
    }

    private function refactorOneToOneAnnotation(Property $property): void
    {
        $oneToOneTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineOneToOneTagValueNode($property);
        if ($oneToOneTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($oneToOneTagValueNode, self::ORPHAN_REMOVAL, false);
        $this->doctrineItemDefaultValueManipulator->remove($oneToOneTagValueNode, self::FETCH, self::LAZY);
    }
}
