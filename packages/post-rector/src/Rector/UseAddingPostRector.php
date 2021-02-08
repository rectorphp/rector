<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use Rector\CodingStyle\Application\UseImportsAdder;
use Rector\CodingStyle\Application\UseImportsRemover;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UseAddingPostRector extends NodeVisitorAbstract implements PostRectorInterface
{
    /**
     * @var UseImportsAdder
     */
    private $useImportsAdder;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var UseImportsRemover
     */
    private $useImportsRemover;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        TypeFactory $typeFactory,
        UseImportsAdder $useImportsAdder,
        UseImportsRemover $useImportsRemover,
        UseNodesToAddCollector $useNodesToAddCollector
    ) {
        $this->useImportsAdder = $useImportsAdder;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportsRemover = $useImportsRemover;
        $this->typeFactory = $typeFactory;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        // no nodes → just return
        if ($nodes === []) {
            return $nodes;
        }

        $smartFileInfo = $this->getSmartFileInfo($nodes);
        if (! $smartFileInfo instanceof SmartFileInfo) {
            return $nodes;
        }

        $useImportTypes = $this->useNodesToAddCollector->getObjectImportsByFileInfo($smartFileInfo);
        $functionUseImportTypes = $this->useNodesToAddCollector->getFunctionImportsByFileInfo($smartFileInfo);
        $removedShortUses = $this->useNodesToAddCollector->getShortUsesByFileInfo($smartFileInfo);

        // nothing to import or remove
        if ($useImportTypes === [] && $functionUseImportTypes === [] && $removedShortUses === []) {
            return $nodes;
        }

        /** @var FullyQualifiedObjectType[] $useImportTypes */
        $useImportTypes = $this->typeFactory->uniquateTypes($useImportTypes);

        $this->useNodesToAddCollector->clear($smartFileInfo);

        // A. has namespace? add under it
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if ($namespace instanceof Namespace_) {
            // first clean
            $this->useImportsRemover->removeImportsFromNamespace($namespace, $removedShortUses);
            // then add, to prevent adding + removing false positive of same short use
            $this->useImportsAdder->addImportsToNamespace($namespace, $useImportTypes, $functionUseImportTypes);

            return $nodes;
        }

        $firstNode = $nodes[0];
        if ($firstNode instanceof FileWithoutNamespace) {
            $nodes = $firstNode->stmts;
        }

        // B. no namespace? add in the top
        // first clean
        $nodes = $this->useImportsRemover->removeImportsFromStmts($nodes, $removedShortUses);
        $useImportTypes = $this->filterOutNonNamespacedNames($useImportTypes);
        // then add, to prevent adding + removing false positive of same short use

        return $this->useImportsAdder->addImportsToStmts($nodes, $useImportTypes, $functionUseImportTypes);
    }

    public function getPriority(): int
    {
        // must be after name importing
        return 500;
    }

    /**
     * @param Node[] $nodes
     */
    private function getSmartFileInfo(array $nodes): ?SmartFileInfo
    {
        foreach ($nodes as $node) {
            /** @var SmartFileInfo|null $smartFileInfo */
            $smartFileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
            if ($smartFileInfo !== null) {
                return $smartFileInfo;
            }
        }

        return null;
    }

    /**
     * Prevents
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @return FullyQualifiedObjectType[]
     */
    private function filterOutNonNamespacedNames(array $useImportTypes): array
    {
        $namespacedUseImportTypes = [];

        foreach ($useImportTypes as $useImportType) {
            if (! Strings::contains($useImportType->getClassName(), '\\')) {
                continue;
            }

            $namespacedUseImportTypes[] = $useImportType;
        }

        return $namespacedUseImportTypes;
    }
}
