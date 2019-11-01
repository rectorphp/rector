<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Application;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use Rector\CodingStyle\Imports\UsedImportsResolver;
use Rector\Contract\PhpParser\Node\CommanderInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class UseAddingCommander implements CommanderInterface
{
    /**
     * @var FullyQualifiedObjectType[][]
     */
    private $useImportTypesInFilePath = [];

    /**
     * @var FullyQualifiedObjectType[][]
     */
    private $functionUseImportTypesInFilePath = [];

    /**
     * @var UseImportsAdder
     */
    private $useImportsAdder;

    /**
     * @var UsedImportsResolver
     */
    private $usedImportsResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var string[][]
     */
    private $removedShortUsesInFilePath = [];

    /**
     * @var UseImportsRemover
     */
    private $useImportsRemover;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    public function __construct(
        UseImportsAdder $useImportsAdder,
        UseImportsRemover $useImportsRemover,
        UsedImportsResolver $usedImportsResolver,
        BetterNodeFinder $betterNodeFinder,
        TypeFactory $typeFactory,
        CurrentFileInfoProvider $currentFileInfoProvider
    ) {
        $this->useImportsAdder = $useImportsAdder;
        $this->usedImportsResolver = $usedImportsResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportsRemover = $useImportsRemover;
        $this->typeFactory = $typeFactory;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    public function addUseImport(Node $positionNode, FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $positionNode->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            // fallback for freshly created Name nodes
            $fileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
            if ($fileInfo === null) {
                throw new ShouldNotHappenException();
            }
        }

        $this->useImportTypesInFilePath[$fileInfo->getRealPath()][] = $fullyQualifiedObjectType;
    }

    public function removeShortUse(Node $node, string $shortUse): void
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            return;
        }

        $this->removedShortUsesInFilePath[$fileInfo->getRealPath()][] = $shortUse;
    }

    public function addFunctionUseImport(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        $this->functionUseImportTypesInFilePath[$fileInfo->getRealPath()][] = $fullyQualifiedObjectType;
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function traverseNodes(array $nodes): array
    {
        // no nodes → just return
        if (count($nodes) === 0) {
            return $nodes;
        }

        $filePath = $this->getRealPathFromNode($nodes[0]);

        $useImportTypes = $this->useImportTypesInFilePath[$filePath] ?? [];
        $functionUseImportTypes = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        $removedShortUses = $this->removedShortUsesInFilePath[$filePath] ?? [];

        // nothing to import or remove
        if ($useImportTypes === [] && $functionUseImportTypes === [] && $removedShortUses === []) {
            return $nodes;
        }

        /** @var FullyQualifiedObjectType[] $useImportTypes */
        $useImportTypes = $this->typeFactory->uniquateTypes($useImportTypes);

        // clear applied imports, so isActive() doesn't return any false positives
        unset($this->useImportTypesInFilePath[$filePath], $this->functionUseImportTypesInFilePath[$filePath]);

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
        // then add, to prevent adding + removing false positive of same short use
        return $this->useImportsAdder->addImportsToStmts($nodes, $useImportTypes, $functionUseImportTypes);
    }

    public function isActive(): bool
    {
        return count($this->useImportTypesInFilePath) > 0 || count($this->functionUseImportTypesInFilePath) > 0;
    }

    public function isShortImported(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $filePath = $this->getRealPathFromNode($node);
        $shortName = $fullyQualifiedObjectType->getShortName();

        $fileUseImports = $this->useImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileUseImports as $fileUseImport) {
            if ($fileUseImport->getShortName() === $shortName) {
                return true;
            }
        }

        $fileFunctionUseImportTypes = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileFunctionUseImportTypes as $fileFunctionUseImportType) {
            if ($fileFunctionUseImportType->getShortName() === $fullyQualifiedObjectType->getShortName()) {
                return true;
            }
        }

        return false;
    }

    public function isImportShortable(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $filePath = $this->getRealPathFromNode($node);

        $fileUseImportTypes = $this->useImportTypesInFilePath[$filePath] ?? [];

        foreach ($fileUseImportTypes as $useImportType) {
            if ($fullyQualifiedObjectType->equals($useImportType)) {
                return true;
            }
        }

        return false;
    }

    public function analyseFileInfoUseStatements(Node $node): void
    {
        $filePath = $this->getRealPathFromNode($node);

        // already analysed
        if (isset($this->useImportTypesInFilePath[$filePath])) {
            return;
        }

        $usedImportTypes = $this->usedImportsResolver->resolveForNode($node);
        foreach ($usedImportTypes as $usedImportType) {
            $this->useImportTypesInFilePath[$filePath][] = $usedImportType;
        }
    }

    public function hasImport(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $useImports = $this->getUseImportTypesByNode($node);

        foreach ($useImports as $useImport) {
            if ($useImport->equals($fullyQualifiedObjectType)) {
                return true;
            }
        }

        return false;
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
        $useImportTypes = $this->getUseImportTypesByNode($node);

        foreach ($useImportTypes as $useImportType) {
            if (! $useImportType->equals($fullyQualifiedObjectType)) {
                if ($useImportType->areShortNamesEqual($fullyQualifiedObjectType)) {
                    return false;
                }
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

    private function getRealPathFromNode(Node $node): ?string
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            return null;
        }

        return $fileInfo->getRealPath();
    }

    /**
     * @return FullyQualifiedObjectType[]
     */
    private function getUseImportTypesByNode(Node $node): array
    {
        $filePath = $this->getRealPathFromNode($node);

        return $this->useImportTypesInFilePath[$filePath] ?? [];
    }
}
