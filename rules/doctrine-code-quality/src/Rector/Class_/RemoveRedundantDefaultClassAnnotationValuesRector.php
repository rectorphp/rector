<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineCodeQuality\NodeAnalyzer\DoctrineClassAnalyzer;
use Rector\DoctrineCodeQuality\NodeManipulator\DoctrineItemDefaultValueManipulator;

/**
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector\RemoveRedundantDefaultClassAnnotationValuesRectorTest
 */
final class RemoveRedundantDefaultClassAnnotationValuesRector extends AbstractRector
{
    /**
     * @var DoctrineClassAnalyzer
     */
    private $doctrineClassAnalyzer;

    /**
     * @var DoctrineItemDefaultValueManipulator
     */
    private $doctrineItemDefaultValueManipulator;

    public function __construct(
        DoctrineClassAnalyzer $doctrineClassAnalyzer,
        DoctrineItemDefaultValueManipulator $doctrineItemDefaultValueManipulator
    ) {
        $this->doctrineClassAnalyzer = $doctrineClassAnalyzer;
        $this->doctrineItemDefaultValueManipulator = $doctrineItemDefaultValueManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes redundant default values from Doctrine ORM annotations on class level',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=false)
 */
class SomeClass
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeClass
{
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->doctrineItemDefaultValueManipulator->resetHasModifiedAnnotation();
        if ($node instanceof Class_) {
            $this->refactorClassAnnotations($node);
        }

        if (! $this->doctrineItemDefaultValueManipulator->hasModifiedAnnotation()) {
            return null;
        }

        return $node;
    }

    private function refactorClassAnnotations(Class_ $class): void
    {
        $this->refactorEntityAnnotation($class);
    }

    private function refactorEntityAnnotation(Class_ $class): void
    {
        $entityTagValueNode = $this->doctrineClassAnalyzer->matchDoctrineEntityTagValueNode($class);
        if ($entityTagValueNode === null) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($entityTagValueNode, 'readOnly', false);
    }
}
