<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Application;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\ClassNameImport\UsedImportsResolver;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

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

        // place after declare strict_types
        foreach ($stmts as $key => $stmt) {
            if ($stmt instanceof Declare_) {
                if (isset($stmts[$key + 1]) && $stmts[$key + 1] instanceof Use_) {
                    $nodesToAdd = $newUses;
                } else {
                    // add extra space, if there are no new use imports to be added
                    $nodesToAdd = array_merge([new Nop()], $newUses);
                }

                array_splice($stmts, $key + 1, 0, $nodesToAdd);

                return $stmts;
            }
        }

        // make use stmts first
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

    /**
     * @param AliasedObjectType[]|FullyQualifiedObjectType[] $useImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     * @return Use_[]
     */
    private function createUses(array $useImportTypes, array $functionUseImportTypes, ?string $namespaceName): array
    {
        $newUses = [];
        foreach ($useImportTypes as $useImportType) {
            if ($namespaceName !== null && $this->isCurrentNamespace($namespaceName, $useImportType)) {
                continue;
            }

            // already imported in previous cycle
            $newUses[] = $useImportType->getUseNode();
        }

        foreach ($functionUseImportTypes as $functionUseImportType) {
            if ($namespaceName !== null && $this->isCurrentNamespace($namespaceName, $functionUseImportType)) {
                continue;
            }

            // already imported in previous cycle
            $newUses[] = $functionUseImportType->getFunctionUseNode();
        }

        return $newUses;
    }

    private function getNamespaceName(Namespace_ $namespace): ?string
    {
        if ($namespace->name === null) {
            return null;
        }

        return $namespace->name->toString();
    }

    private function isCurrentNamespace(string $namespaceName, ObjectType $objectType): bool
    {
        $afterCurrentNamespace = Strings::after($objectType->getClassName(), $namespaceName . '\\');
        if (! $afterCurrentNamespace) {
            return false;
        }

        return ! Strings::contains($afterCurrentNamespace, '\\');
    }
}
