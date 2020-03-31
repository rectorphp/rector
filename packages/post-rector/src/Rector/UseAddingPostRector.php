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
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UseAddingPostRector extends NodeVisitorAbstract implements RectorInterface
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
        UseImportsAdder $useImportsAdder,
        UseImportsRemover $useImportsRemover,
        BetterNodeFinder $betterNodeFinder,
        TypeFactory $typeFactory,
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
    public function traverse(array $nodes): array
    {
        // no nodes → just return
        if (count($nodes) === 0) {
            return $nodes;
        }

        $filePath = $this->getRealPathFromNode($nodes[0]);

        if ($filePath === null) {
            return $nodes;
        }

        $useImportTypes = $this->useNodesToAddCollector->getUseImportTypes($filePath);
        $functionUseImportTypes = $this->useNodesToAddCollector->getFunctionUseImportTypesInFilePath($filePath);
        $removedShortUses = $this->useNodesToAddCollector->getShortUsesInFilePath($filePath);

        // nothing to import or remove
        if ($useImportTypes === [] && $functionUseImportTypes === [] && $removedShortUses === []) {
            return $nodes;
        }

        /** @var FullyQualifiedObjectType[] $useImportTypes */
        $useImportTypes = $this->typeFactory->uniquateTypes($useImportTypes);

        $this->useNodesToAddCollector->clear($filePath);

        // A. has namespace? add under it
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if ($namespace instanceof Namespace_) {
            // first clean
            $this->useImportsRemover->removeImportsFromNamespace($namespace, $removedShortUses);
            // then add, to prevent adding + removing false positive of same short use
            $this->useImportsAdder->addImportsToNamespace($namespace, $useImportTypes, $functionUseImportTypes);

            return $nodes;
        }

        // B. no namespace? add in the top
        // first clean
        $nodes = $this->useImportsRemover->removeImportsFromStmts($nodes, $removedShortUses);
        $useImportTypes = $this->filterOutNonNamespacedNames($useImportTypes);
        // then add, to prevent adding + removing false positive of same short use

        return $this->useImportsAdder->addImportsToStmts($nodes, $useImportTypes, $functionUseImportTypes);
    }

    /**
     * This prevents importing:
     * - App\Some\Product
     *
     * if there is already:
     * - use App\Another\Product
     */
    public function canImportBeAdded(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $useImportTypes = $this->useNodesToAddCollector->getUseImportTypesByNode($node);

        foreach ($useImportTypes as $useImportType) {
            if (! $useImportType->equals($fullyQualifiedObjectType) &&
                $useImportType->areShortNamesEqual($fullyQualifiedObjectType)
            ) {
                return false;
            }

            if ($useImportType->equals($fullyQualifiedObjectType)) {
                return true;
            }
        }

        return true;
    }

    public function getPriority(): int
    {
        return 500;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Post Rector that adds use statements');
    }

    public function refactor(Node $node): ?Node
    {
        // note: traverse() is used instead
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

    private function getRealPathFromNode(Node $node): ?string
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            return null;
        }

        return $fileInfo->getRealPath();
    }
}
