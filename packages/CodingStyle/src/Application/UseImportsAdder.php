<?php declare(strict_types=1);

namespace Rector\CodingStyle\Application;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\Imports\UsedImportsResolver;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class UseImportsAdder
{
    /**
     * @var UsedImportsResolver
     */
    private $usedImportsResolver;

    public function __construct(UsedImportsResolver $usedImportsResolver)
    {
        $this->usedImportsResolver = $usedImportsResolver;
    }

    /**
     * @param Stmt[] $stmts
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     * @return Stmt[]
     */
    public function addImportsToStmts(array $stmts, array $useImportTypes, array $functionUseImportTypes): array
    {
        $existingUseImportTypes = $this->usedImportsResolver->resolveForStmts($stmts);
        $existingFunctionUseImports = $this->usedImportsResolver->resolveFunctionImportsForStmts($stmts);

        $useImportTypes = $this->diffFullyQualifiedObjectTypes($useImportTypes, $existingUseImportTypes);
        $functionUseImportTypes = $this->diffFullyQualifiedObjectTypes(
            $functionUseImportTypes,
            $existingFunctionUseImports
        );

        $newUses = $this->createUses($useImportTypes, $functionUseImportTypes, null);

        return array_merge($newUses, $stmts);
    }

    /**
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     */
    public function addImportsToNamespace(
        Namespace_ $namespace,
        array $useImportTypes,
        array $functionUseImportTypes
    ): void {
        $namespaceName = $this->getNamespaceName($namespace);

        $existingUseImportTypes = $this->usedImportsResolver->resolveForNode($namespace);
        $existingFunctionUseImportTypes = $this->usedImportsResolver->resolveFunctionImportsForStmts($namespace->stmts);

        $useImportTypes = $this->diffFullyQualifiedObjectTypes($useImportTypes, $existingUseImportTypes);
        $functionUseImportTypes = $this->diffFullyQualifiedObjectTypes(
            $functionUseImportTypes,
            $existingFunctionUseImportTypes
        );

        $newUses = $this->createUses($useImportTypes, $functionUseImportTypes, $namespaceName);
        $namespace->stmts = array_merge($newUses, $namespace->stmts);
    }

    private function getNamespaceName(Namespace_ $namespace): ?string
    {
        if ($namespace->name === null) {
            return null;
        }

        return $namespace->name->toString();
    }

    private function isCurrentNamespace(
        string $namespaceName,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        if ($namespaceName === null) {
            return false;
        }

        $afterCurrentNamespace = Strings::after($fullyQualifiedObjectType->getClassName(), $namespaceName . '\\');
        if (! $afterCurrentNamespace) {
            return false;
        }

        return ! Strings::contains($afterCurrentNamespace, '\\');
    }

    /**
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     * @return Use_[]
     */
    private function createUses(array $useImportTypes, array $functionUseImportTypes, ?string $namespaceName): array
    {
        if ($namespaceName === null) {
            // not supported yet
            return [];
        }

        $newUses = [];
        foreach ($useImportTypes as $useImportType) {
            if ($this->isCurrentNamespace($namespaceName, $useImportType)) {
                continue;
            }

            // already imported in previous cycle
            $newUses[] = $useImportType->getUseNode();
        }

        foreach ($functionUseImportTypes as $functionUseImportType) {
            if ($this->isCurrentNamespace($namespaceName, $functionUseImportType)) {
                continue;
            }

            // already imported in previous cycle
            $newUses[] = $functionUseImportType->getFunctionUseNode();
        }

        return $newUses;
    }

    /**
     * @param FullyQualifiedObjectType[] $mainTypes
     * @param FullyQualifiedObjectType[] $typesToRemove
     * @return FullyQualifiedObjectType[]
     */
    private function diffFullyQualifiedObjectTypes(array $mainTypes, array $typesToRemove): array
    {
        foreach ($mainTypes as $key => $mainType) {
            foreach ($typesToRemove as $typeToRemove) {
                if ($mainType->equals($typeToRemove)) {
                    unset($mainTypes[$key]);
                }
            }
        }

        return array_values($mainTypes);
    }
}
