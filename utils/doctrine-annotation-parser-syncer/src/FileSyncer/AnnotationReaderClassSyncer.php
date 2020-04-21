<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer;

use Rector\Utils\DoctrineAnnotationParserSyncer\ClassSyncerNodeTraverser;

final class AnnotationReaderClassSyncer extends AbstractClassSyncer
{
    /**
     * @var ClassSyncerNodeTraverser
     */
    private $classSyncerNodeTraverser;

    public function __construct(ClassSyncerNodeTraverser $classSyncerNodeTraverser)
    {
        $this->classSyncerNodeTraverser = $classSyncerNodeTraverser;
    }

    public function sync(bool $isDryRun): bool
    {
        $nodes = $this->getFileNodes();
        $changedNodes = $this->classSyncerNodeTraverser->traverse($nodes);

        if ($isDryRun) {
            return ! $this->hasContentChanged($nodes);
        }

        $this->printNodesToPath($changedNodes);
        return true;
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
