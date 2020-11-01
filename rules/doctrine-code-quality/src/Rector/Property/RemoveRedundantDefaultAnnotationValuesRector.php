<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineCodeQuality\NodeAnalyzer\DoctrineClassAnalyzer;
use Rector\DoctrineCodeQuality\NodeAnalyzer\DoctrinePropertyAnalyzer;

/**
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\RemoveRedundantDefaultAnnotationValuesRectorTest
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Class_\RemoveRedundantDefaultAnnotationValuesRector\RemoveRedundantDefaultAnnotationValuesRectorTest
 */
final class RemoveRedundantDefaultAnnotationValuesRector extends AbstractRector
{
    /**
     * @var DoctrinePropertyAnalyzer
     */
    private $doctrinePropertyAnalyzer;

    /**
     * @var DoctrineClassAnalyzer
     */
    private $doctrineClassAnalyzer;

    /**
     * @var bool
     */
    private $hasModifiedAnnotation = false;

    public function __construct(
        DoctrinePropertyAnalyzer $doctrinePropertyAnalyzer,
        DoctrineClassAnalyzer $doctrineClassAnalyzer
    ) {
        $this->doctrinePropertyAnalyzer = $doctrinePropertyAnalyzer;
        $this->doctrineClassAnalyzer = $doctrineClassAnalyzer;

    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes redundant default values from Doctrine ORM annotations.',
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
        return [Class_::class, Property::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Property) {
            $this->refactorPropertyAnnotations($node);
        }

        if ($node instanceof Class_) {
            $this->refactorClassAnnotations($node);
        }

        if (! $this->hasModifiedAnnotation) {
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

        $this->removeItemWithDefaultValue($columnTagValueNode, 'nullable', false);
        $this->removeItemWithDefaultValue($columnTagValueNode, 'unique', false);
        $this->removeItemWithDefaultValue($columnTagValueNode, 'precision', 0);
        $this->removeItemWithDefaultValue($columnTagValueNode, 'scale', 0);
    }

    private function refactorJoinColumnAnnotation(Property $node): void
    {
        $joinColumnTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineJoinColumnTagValueNode($node);
        if ($joinColumnTagValueNode === null) {
            return;
        }

        $this->removeItemWithDefaultValue($joinColumnTagValueNode, 'nullable', true);
        $this->removeItemWithDefaultValue($joinColumnTagValueNode, 'referencedColumnName', 'id');
        $this->removeItemWithDefaultValue($joinColumnTagValueNode, 'unique', false);
    }

    private function refactorGeneratedValueAnnotation(Property $node): void
    {
        $generatedValue = $this->doctrinePropertyAnalyzer->matchDoctrineGeneratedValueTagValueNode($node);
        if ($generatedValue === null) {
            return;
        }

        $this->removeItemWithDefaultValue($generatedValue, 'strategy', 'AUTO');
    }

    private function refactorManyToManyAnnotation(Property $node): void
    {
        $manyToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineManyToManyTagValueNode($node);
        if ($manyToManyTagValueNode === null) {
            return;
        }

        $this->removeItemWithDefaultValue($manyToManyTagValueNode, 'orphanRemoval', false);
        $this->removeItemWithDefaultValue($manyToManyTagValueNode, 'fetch', 'LAZY');
    }

    private function refactorManyToOneAnnotation(Property $node): void
    {
        $manyToOneTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineManyToOneTagValueNode($node);
        if ($manyToOneTagValueNode === null) {
            return;
        }

        $this->removeItemWithDefaultValue($manyToOneTagValueNode, 'fetch', 'LAZY');
    }

    private function refactorOneToManyAnnotation(Property $node): void
    {
        $oneToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineOneToManyTagValueNode($node);
        if ($oneToManyTagValueNode === null) {
            return;
        }

        $this->removeItemWithDefaultValue($oneToManyTagValueNode, 'orphanRemoval', false);
        $this->removeItemWithDefaultValue($oneToManyTagValueNode, 'fetch', 'LAZY');
    }

    private function refactorOneToOneAnnotation(Property $node): void
    {
        $oneToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineOneToOneTagValueNode($node);
        if ($oneToManyTagValueNode === null) {
            return;
        }

        $this->removeItemWithDefaultValue($oneToManyTagValueNode, 'orphanRemoval', false);
        $this->removeItemWithDefaultValue($oneToManyTagValueNode, 'fetch', 'LAZY');
    }

    private function refactorClassAnnotations(Class_ $node): void
    {
        $this->refactorEntityAnnotation($node);
    }

    private function refactorEntityAnnotation(Class_ $node): void
    {
        $entityTagValueNode = $this->doctrineClassAnalyzer->matchDoctrineEntityTagValueNode($node);
        if ($entityTagValueNode === null) {
            return;
        }

        $this->removeItemWithDefaultValue($entityTagValueNode, 'readOnly', false);
    }

    /**
     * @param bool|string|int $defaultValue
     */
    private function removeItemWithDefaultValue(
        AbstractDoctrineTagValueNode $doctrineTagValueNode,
        string $item,
        $defaultValue
    ): void {
        if (! isset($doctrineTagValueNode->getAttributableItems()[$item])) {
            return;
        }

        if ($doctrineTagValueNode->getAttributableItems()[$item] === $defaultValue) {
            $this->hasModifiedAnnotation = true;
            $doctrineTagValueNode->removeItem($item);
        }
    }
}
