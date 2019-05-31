<?php declare(strict_types=1);

namespace Rector\CodingStyle\Application;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use Rector\CodingStyle\Imports\UsedImportsResolver;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Contract\PhpParser\Node\CommanderInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class UseAddingCommander implements CommanderInterface
{
    /**
     * @var string[][]
     */
    private $useImportsInFilePath = [];

    /**
     * @var string[][]
     */
    private $functionUseImportsInFilePath = [];

    /**
     * @var UseImportsAdder
     */
    private $useImportsAdder;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var UsedImportsResolver
     */
    private $usedImportsResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        UseImportsAdder $useImportsAdder,
        ClassNaming $classNaming,
        UsedImportsResolver $usedImportsResolver,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->useImportsAdder = $useImportsAdder;
        $this->classNaming = $classNaming;
        $this->usedImportsResolver = $usedImportsResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function addUseImport(Node $node, string $useImport): void
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);

        if ($fileInfo === null) {
            return;
        }

        $this->useImportsInFilePath[$fileInfo->getRealPath()][] = $useImport;
    }

    public function addFunctionUseImport(Node $node, string $functionUseImport): void
    {
        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        $this->functionUseImportsInFilePath[$fileInfo->getRealPath()][] = $functionUseImport;
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function traverseNodes(array $nodes): array
    {
        // no nodes → just return
        if (! isset($nodes[0])) {
            return $nodes;
        }

        $filePath = $this->getRealPathFromNode($nodes[0]);

        $useImports = $this->useImportsInFilePath[$filePath] ?? [];
        $functionUseImports = $this->functionUseImportsInFilePath[$filePath] ?? [];

        // nothing to import
        if ($useImports === [] && $functionUseImports === []) {
            return $nodes;
        }

        // clear applied imports, so isActive() doesn't return any false positives
        unset($this->useImportsInFilePath[$filePath], $this->functionUseImportsInFilePath[$filePath]);

        // A. has namespace? add under it
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if ($namespace instanceof Namespace_) {
            $this->useImportsAdder->addImportsToNamespace($namespace, $useImports, $functionUseImports);
            return $nodes;
        }

        // B. no namespace? add in the top
        return $this->useImportsAdder->addImportsToStmts($nodes, $useImports, $functionUseImports);
    }

    public function isActive(): bool
    {
        return count($this->useImportsInFilePath) > 0 || count($this->functionUseImportsInFilePath) > 0;
    }

    public function isShortImported(Node $node, string $fullyQualifiedName): bool
    {
        $filePath = $this->getRealPathFromNode($node);
        $shortName = $this->classNaming->getShortName($fullyQualifiedName);

        $fileUseImports = $this->useImportsInFilePath[$filePath] ?? [];
        foreach ($fileUseImports as $fileUseImport) {
            $shortFileUseImport = $this->classNaming->getShortName($fileUseImport);
            if ($shortFileUseImport === $shortName) {
                return true;
            }
        }

        $fileFunctionUseImports = $this->functionUseImportsInFilePath[$filePath] ?? [];
        foreach ($fileFunctionUseImports as $fileFunctionUseImport) {
            $shortFileFunctionUseImport = $this->classNaming->getShortName($fileFunctionUseImport);
            if ($shortFileFunctionUseImport === $shortName) {
                return true;
            }
        }

        return false;
    }

    public function isImportShortable(Node $node, string $fullyQualifiedName): bool
    {
        $filePath = $this->getRealPathFromNode($node);

        $fileUseImports = $this->useImportsInFilePath[$filePath] ?? [];
        if (in_array($fullyQualifiedName, $fileUseImports, true)) {
            return true;
        }

        $functionUseImports = $this->functionUseImportsInFilePath[$filePath] ?? [];
        if (in_array($fullyQualifiedName, $functionUseImports, true)) {
            return true;
        }

        return false;
    }

    public function analyseFileInfoUseStatements(Node $node): void
    {
        $filePath = $this->getRealPathFromNode($node);

        // already analysed
        if (isset($this->useImportsInFilePath[$filePath])) {
            return;
        }

        $usedImports = $this->usedImportsResolver->resolveForNode($node);

        foreach ($usedImports as $usedImport) {
            $this->useImportsInFilePath[$filePath][] = $usedImport;
        }
    }

    public function hasImport(Name $name, string $fullyQualifiedName): bool
    {
        $filePath = $this->getRealPathFromNode($name);

        return in_array($fullyQualifiedName, $this->useImportsInFilePath[$filePath] ?? [], true);
    }

    public function canImportBeAdded(Name $name, string $import): bool
    {
        $shortImport = $this->classNaming->getShortName($import);

        $filePath = $this->getRealPathFromNode($name);

        foreach ($this->useImportsInFilePath[$filePath] ?? [] as $importsInClass) {
            $shortImportInClass = $this->classNaming->getShortName($importsInClass);
            if ($importsInClass !== $import && $shortImportInClass === $shortImport) {
                return true;
            }
        }

        return false;
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
