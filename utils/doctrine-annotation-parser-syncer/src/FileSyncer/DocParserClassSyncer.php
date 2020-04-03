<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer;

use PhpParser\NodeTraverser;
use Rector\Utils\DoctrineAnnotationParserSyncer\Rector\ClassMethod\RemoveValueChangeFromConstantClassMethodRector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Dir\ReplaceDirWithRealPathRector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Namespace_\RenameDocParserClassRector;

final class DocParserClassSyncer extends AbstractClassSyncer
{
    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(
        RenameDocParserClassRector $renameDocParserClassRector,
        RemoveValueChangeFromConstantClassMethodRector $removeValueChangeFromConstantClassMethodRector,
        ReplaceDirWithRealPathRector $replaceDirWithRealPathRector
    ) {
        $this->nodeTraverser = new NodeTraverser();
        $this->nodeTraverser->addVisitor($renameDocParserClassRector);
        $this->nodeTraverser->addVisitor($removeValueChangeFromConstantClassMethodRector);
        $this->nodeTraverser->addVisitor($replaceDirWithRealPathRector);
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
        return __DIR__ . '/../../../../vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/DocParser.php';
    }

    public function getTargetFilePath(): string
    {
        return __DIR__ . '/../../../../packages/doctrine-annotation-generated/src/ConstantPreservingDocParser.php';
    }
}
