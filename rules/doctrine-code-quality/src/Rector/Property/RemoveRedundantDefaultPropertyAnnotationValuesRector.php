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

    private function refactorPropertyAnnotations(Property $node): void
    {
        $this->refactorColumnAnnotation($node);
        $this->refactorGeneratedValueAnnotation($node);
        $this->refactorJoinColumnAnnotation($node);
        $this->refactorManyToManyAnnotation($node);
        $this->refactorManyToOneAnnotation($node);
        $this->refactorOneToManyAnnotation($node);
        $this->refactorOneToOneAnnotation($node);
    }

    private function refactorColumnAnnotation(Property $node): void
    {
        $columnTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineColumnTagValueNode($node);
        if ($columnTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($columnTagValueNode, 'nullable', false);
        $this->doctrineItemDefaultValueManipulator->remove($columnTagValueNode, 'unique', false);
        $this->doctrineItemDefaultValueManipulator->remove($columnTagValueNode, 'precision', 0);
        $this->doctrineItemDefaultValueManipulator->remove($columnTagValueNode, 'scale', 0);
    }

    private function refactorJoinColumnAnnotation(Property $node): void
    {
        $joinColumnTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineJoinColumnTagValueNode($node);
        if ($joinColumnTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($joinColumnTagValueNode, 'nullable', true);
        $this->doctrineItemDefaultValueManipulator->remove($joinColumnTagValueNode, 'referencedColumnName', 'id');
        $this->doctrineItemDefaultValueManipulator->remove($joinColumnTagValueNode, 'unique', false);
    }

    private function refactorGeneratedValueAnnotation(Property $node): void
    {
        $generatedValue = $this->doctrinePropertyAnalyzer->matchDoctrineGeneratedValueTagValueNode($node);
        if ($generatedValue === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($generatedValue, 'strategy', 'AUTO');
    }

    private function refactorManyToManyAnnotation(Property $node): void
    {
        $manyToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineManyToManyTagValueNode($node);
        if ($manyToManyTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($manyToManyTagValueNode, 'orphanRemoval', false);
        $this->doctrineItemDefaultValueManipulator->remove($manyToManyTagValueNode, 'fetch', 'LAZY');
    }

    private function refactorManyToOneAnnotation(Property $node): void
    {
        $manyToOneTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineManyToOneTagValueNode($node);
        if ($manyToOneTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($manyToOneTagValueNode, 'fetch', 'LAZY');
    }

    private function refactorOneToManyAnnotation(Property $node): void
    {
        $oneToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineOneToManyTagValueNode($node);
        if ($oneToManyTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($oneToManyTagValueNode, 'orphanRemoval', false);
        $this->doctrineItemDefaultValueManipulator->remove($oneToManyTagValueNode, 'fetch', 'LAZY');
    }

    private function refactorOneToOneAnnotation(Property $node): void
    {
        $oneToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineOneToOneTagValueNode($node);
        if ($oneToManyTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($oneToManyTagValueNode, 'orphanRemoval', false);
        $this->doctrineItemDefaultValueManipulator->remove($oneToManyTagValueNode, 'fetch', 'LAZY');
    }
}
