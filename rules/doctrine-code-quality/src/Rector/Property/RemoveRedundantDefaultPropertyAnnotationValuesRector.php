<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\DoctrineCodeQuality\NodeManipulator\DoctrineItemDefaultValueManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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
     * @var DoctrineItemDefaultValueManipulator
     */
    private $doctrineItemDefaultValueManipulator;

    public function __construct(DoctrineItemDefaultValueManipulator $doctrineItemDefaultValueManipulator)
    {
        $this->doctrineItemDefaultValueManipulator = $doctrineItemDefaultValueManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
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
        if ($node instanceof Property) {
            $this->refactorPropertyAnnotations($node);
        }

        return $node;
    }

    private function refactorPropertyAnnotations(Property $property): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $this->refactorColumnAnnotation($phpDocInfo);
        $this->refactorGeneratedValueAnnotation($phpDocInfo);
        $this->refactorJoinColumnAnnotation($phpDocInfo);
        $this->refactorManyToManyAnnotation($phpDocInfo);
        $this->refactorManyToOneAnnotation($phpDocInfo);
        $this->refactorOneToManyAnnotation($phpDocInfo);
        $this->refactorOneToOneAnnotation($phpDocInfo);
    }

    private function refactorColumnAnnotation(PhpDocInfo $phpDocInfo): void
    {
        $columnTagValueNode = $phpDocInfo->getByType(ColumnTagValueNode::class);
        if (! $columnTagValueNode instanceof ColumnTagValueNode) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($phpDocInfo, $columnTagValueNode, 'nullable', false);
        $this->doctrineItemDefaultValueManipulator->remove($phpDocInfo, $columnTagValueNode, 'unique', false);
        $this->doctrineItemDefaultValueManipulator->remove($phpDocInfo, $columnTagValueNode, 'precision', 0);
        $this->doctrineItemDefaultValueManipulator->remove($phpDocInfo, $columnTagValueNode, 'scale', 0);
    }

    private function refactorGeneratedValueAnnotation(PhpDocInfo $phpDocInfo): void
    {
        $generatedValueTagValueNode = $phpDocInfo->getByType(GeneratedValueTagValueNode::class);
        if (! $generatedValueTagValueNode instanceof GeneratedValueTagValueNode) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove(
            $phpDocInfo,
            $generatedValueTagValueNode,
            'strategy',
            'AUTO'
        );
    }

    private function refactorJoinColumnAnnotation(PhpDocInfo $phpDocInfo): void
    {
        $joinColumnTagValueNode = $phpDocInfo->getByType(JoinColumnTagValueNode::class);
        if (! $joinColumnTagValueNode instanceof JoinColumnTagValueNode) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove($phpDocInfo, $joinColumnTagValueNode, 'nullable', true);
        $this->doctrineItemDefaultValueManipulator->remove(
            $phpDocInfo,
            $joinColumnTagValueNode,
            'referencedColumnName',
            'id'
        );
        $this->doctrineItemDefaultValueManipulator->remove($phpDocInfo, $joinColumnTagValueNode, 'unique', false);
    }

    private function refactorManyToManyAnnotation(PhpDocInfo $phpDocInfo): void
    {
        $manyToManyTagValueNode = $phpDocInfo->getByType(ManyToManyTagValueNode::class);
        if (! $manyToManyTagValueNode instanceof ManyToManyTagValueNode) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove(
            $phpDocInfo,
            $manyToManyTagValueNode,
            self::ORPHAN_REMOVAL,
            false
        );
        $this->doctrineItemDefaultValueManipulator->remove(
            $phpDocInfo,
            $manyToManyTagValueNode,
            self::FETCH,
            self::LAZY
        );
    }

    private function refactorManyToOneAnnotation(PhpDocInfo $phpDocInfo): void
    {
        $manyToOneTagValueNode = $phpDocInfo->getByType(ManyToOneTagValueNode::class);
        if (! $manyToOneTagValueNode instanceof ManyToOneTagValueNode) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove(
            $phpDocInfo,
            $manyToOneTagValueNode,
            self::FETCH,
            self::LAZY
        );
    }

    private function refactorOneToManyAnnotation(PhpDocInfo $phpDocInfo): void
    {
        $oneToManyTagValueNode = $phpDocInfo->getByType(OneToManyTagValueNode::class);
        if (! $oneToManyTagValueNode instanceof OneToManyTagValueNode) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove(
            $phpDocInfo,
            $oneToManyTagValueNode,
            self::ORPHAN_REMOVAL,
            false
        );
        $this->doctrineItemDefaultValueManipulator->remove(
            $phpDocInfo,
            $oneToManyTagValueNode,
            self::FETCH,
            self::LAZY
        );
    }

    private function refactorOneToOneAnnotation(PhpDocInfo $phpDocInfo): void
    {
        $oneToOneTagValueNode = $phpDocInfo->getByType(OneToOneTagValueNode::class);
        if (! $oneToOneTagValueNode instanceof OneToOneTagValueNode) {
            return;
        }

        $this->doctrineItemDefaultValueManipulator->remove(
            $phpDocInfo,
            $oneToOneTagValueNode,
            self::ORPHAN_REMOVAL,
            false
        );
        $this->doctrineItemDefaultValueManipulator->remove($phpDocInfo, $oneToOneTagValueNode, self::FETCH, self::LAZY);
    }
}
