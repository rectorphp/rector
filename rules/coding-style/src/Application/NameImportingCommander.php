<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Application;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\PhpParser\Node\CommanderInterface;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class NameImportingCommander implements CommanderInterface
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var NameImporter
     */
    private $nameImporter;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var bool
     */
    private $importDocBlocks = false;

    /**
     * @var DocBlockNameImporter
     */
    private $docBlockNameImporter;

    public function __construct(
        ParameterProvider $parameterProvider,
        NameImporter $nameImporter,
        CallableNodeTraverser $callableNodeTraverser,
        DocBlockNameImporter $docBlockNameImporter,
        bool $importDocBlocks
    ) {
        $this->parameterProvider = $parameterProvider;
        $this->nameImporter = $nameImporter;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->importDocBlocks = $importDocBlocks;
        $this->docBlockNameImporter = $docBlockNameImporter;
    }

    public function isActive(): bool
    {
        return (bool) $this->parameterProvider->provideParameter(Option::AUTO_IMPORT_NAMES);
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodes(array $nodes): array
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node): ?Node {
            if ($node instanceof Name) {
                return $this->nameImporter->importName($node);
            }

            if ($this->importDocBlocks) {
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

            return null;
        });

        return $nodes;
    }

    public function getPriority(): int
    {
        return 600;
    }
}
