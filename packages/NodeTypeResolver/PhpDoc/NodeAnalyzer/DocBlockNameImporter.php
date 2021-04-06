<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class DocBlockNameImporter
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var ClassNameImportSkipper
     */
    private $classNameImportSkipper;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    /**
     * @var NameImportingPhpDocNodeVisitor
     */
    private $nameImportingPhpDocNodeVisitor;

    public function __construct(
        ClassNameImportSkipper $classNameImportSkipper,
        ParameterProvider $parameterProvider,
        PhpDocNodeTraverser $phpDocNodeTraverser,
        StaticTypeMapper $staticTypeMapper,
        UseNodesToAddCollector $useNodesToAddCollector,
        NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor
    ) {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->parameterProvider = $parameterProvider;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->nameImportingPhpDocNodeVisitor = $nameImportingPhpDocNodeVisitor;
    }

    public function importNames(PhpDocNode $phpDocNode, \PhpParser\Node $node): void
    {
        if ($phpDocNode->children === []) {
            return;
        }

        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $this->nameImportingPhpDocNodeVisitor->setCurrentNode($node);

        $phpDocNodeTraverser->addPhpDocNodeVisitor($this->nameImportingPhpDocNodeVisitor);
        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
