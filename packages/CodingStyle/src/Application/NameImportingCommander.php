<?php declare(strict_types=1);

namespace Rector\CodingStyle\Application;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Configuration\Option;
use Rector\Contract\PhpParser\Node\CommanderInterface;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
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

    public function __construct(
        ParameterProvider $parameterProvider,
        NameImporter $nameImporter,
        CallableNodeTraverser $callableNodeTraverser
    ) {
        $this->parameterProvider = $parameterProvider;
        $this->nameImporter = $nameImporter;
        $this->callableNodeTraverser = $callableNodeTraverser;
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
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node): ?Name {
            if (! $node instanceof Name) {
                return null;
            }

            return $this->nameImporter->importName($node);
        });

        return $nodes;
    }

    public function getPriority(): int
    {
        return 600;
    }
}
