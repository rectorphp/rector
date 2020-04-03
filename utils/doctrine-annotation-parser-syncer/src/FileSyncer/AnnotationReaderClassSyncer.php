<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer;

use PhpParser\NodeTraverser;
use Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Assign\AssignNewDocParserRector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Rector\ClassMethod\ChangeOriginalTypeToCustomRector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Dir\ReplaceDirWithRealPathRector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Namespace_\RenameAnnotationReaderClassRector;

final class AnnotationReaderClassSyncer extends AbstractClassSyncer
{
    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(
        RenameAnnotationReaderClassRector $renameAnnotationReaderClassRector,
        ChangeOriginalTypeToCustomRector $changeOriginalTypeToCustomRector,
        ReplaceDirWithRealPathRector $replaceDirWithRealPathRector,
        AssignNewDocParserRector $assignNewDocParserRector
    ) {
        $this->nodeTraverser = new NodeTraverser();
        $this->nodeTraverser->addVisitor($renameAnnotationReaderClassRector);
        $this->nodeTraverser->addVisitor($changeOriginalTypeToCustomRector);
        $this->nodeTraverser->addVisitor($replaceDirWithRealPathRector);
        $this->nodeTraverser->addVisitor($assignNewDocParserRector);
    }

    public function sync(): void
    {
        $nodes = $this->getFileNodes();
        $changedNodes = $this->nodeTraverser->traverse($nodes);
        $this->printNodesToPath($changedNodes);

        $this->reportChange();
    }

    public function getSourceFilePath(): string
    {
        return __DIR__ . '/../../../../vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationReader.php';
    }

    public function getTargetFilePath(): string
    {
        return __DIR__ . '/../../../../packages/doctrine-annotation-generated/src/ConstantPreservingAnnotationReader.php';
    }
}
