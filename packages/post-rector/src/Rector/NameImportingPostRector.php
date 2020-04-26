<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Core\Configuration\Option;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class NameImportingPostRector extends AbstractPostRector
{
    /**
     * @var bool
     */
    private $importDocBlocks = false;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var NameImporter
     */
    private $nameImporter;

    /**
     * @var DocBlockNameImporter
     */
    private $docBlockNameImporter;

    public function __construct(
        ParameterProvider $parameterProvider,
        NameImporter $nameImporter,
        DocBlockNameImporter $docBlockNameImporter,
        bool $importDocBlocks
    ) {
        $this->parameterProvider = $parameterProvider;
        $this->nameImporter = $nameImporter;
        $this->importDocBlocks = $importDocBlocks;
        $this->docBlockNameImporter = $docBlockNameImporter;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $this->parameterProvider->provideParameter(Option::AUTO_IMPORT_NAMES)) {
            return null;
        }

        if ($node instanceof Name) {
            return $this->nameImporter->importName($node);
        }

        if (! $this->importDocBlocks) {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $hasChanged = $this->docBlockNameImporter->importNames($phpDocInfo, $node);
        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    public function getPriority(): int
    {
        return 600;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Imports names');
    }
}
