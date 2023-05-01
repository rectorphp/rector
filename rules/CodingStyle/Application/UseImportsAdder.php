<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Application;

use RectorPrefix202305\Nette\Utils\Strings;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\ClassNameImport\UsedImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UseImportsAdder
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\UsedImportsResolver
     */
    private $usedImportsResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(UsedImportsResolver $usedImportsResolver, TypeFactory $typeFactory)
    {
        $this->usedImportsResolver = $usedImportsResolver;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param Stmt[] $stmts
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $useImportTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $functionUseImportTypes
     * @return Stmt[]
     */
    public function addImportsToStmts(array $stmts, array $useImportTypes, array $functionUseImportTypes) : array
    {
        $existingUseImportTypes = $this->usedImportsResolver->resolveForStmts($stmts);
        $existingFunctionUseImports = $this->usedImportsResolver->resolveFunctionImportsForStmts($stmts);
        $useImportTypes = $this->diffFullyQualifiedObjectTypes($useImportTypes, $existingUseImportTypes);
        $functionUseImportTypes = $this->diffFullyQualifiedObjectTypes($functionUseImportTypes, $existingFunctionUseImports);
        $newUses = $this->createUses($useImportTypes, $functionUseImportTypes, null);
        if ($newUses === []) {
            return $stmts;
        }
        // place after declare strict_types
        foreach ($stmts as $key => $stmt) {
            if ($stmt instanceof Declare_) {
                if (isset($stmts[$key + 1]) && $stmts[$key + 1] instanceof Use_) {
                    $nodesToAdd = $newUses;
                } else {
                    // add extra space, if there are no new use imports to be added
                    $nodesToAdd = \array_merge([new Nop()], $newUses);
                }
                $this->mirrorUseComments($stmts, $newUses, $key + 1);
                \array_splice($stmts, $key + 1, 0, $nodesToAdd);
                return $stmts;
            }
        }
        $this->mirrorUseComments($stmts, $newUses);
        // make use stmts first
        return \array_merge($newUses, $stmts);
    }
    /**
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     */
    public function addImportsToNamespace(Namespace_ $namespace, array $useImportTypes, array $functionUseImportTypes) : void
    {
        $namespaceName = $this->getNamespaceName($namespace);
        $existingUseImportTypes = $this->usedImportsResolver->resolveForNode($namespace);
        $existingFunctionUseImportTypes = $this->usedImportsResolver->resolveFunctionImportsForStmts($namespace->stmts);
        $existingUseImportTypes = $this->typeFactory->uniquateTypes($existingUseImportTypes);
        $useImportTypes = $this->diffFullyQualifiedObjectTypes($useImportTypes, $existingUseImportTypes);
        $functionUseImportTypes = $this->diffFullyQualifiedObjectTypes($functionUseImportTypes, $existingFunctionUseImportTypes);
        $newUses = $this->createUses($useImportTypes, $functionUseImportTypes, $namespaceName);
        if ($newUses === []) {
            return;
        }
        $this->mirrorUseComments($namespace->stmts, $newUses);
        $namespace->stmts = \array_merge($newUses, $namespace->stmts);
    }
    /**
     * @param Stmt[] $stmts
     * @param Use_[] $newUses
     */
    private function mirrorUseComments(array $stmts, array $newUses, int $indexStmt = 0) : void
    {
        if ($stmts === []) {
            return;
        }
        if ($stmts[$indexStmt] instanceof Use_) {
            $comments = (array) $stmts[$indexStmt]->getAttribute(AttributeKey::COMMENTS);
            if ($comments !== []) {
                $newUses[0]->setAttribute(AttributeKey::COMMENTS, $stmts[$indexStmt]->getAttribute(AttributeKey::COMMENTS));
                $stmts[$indexStmt]->setAttribute(AttributeKey::COMMENTS, null);
            }
        }
    }
    /**
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $mainTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $typesToRemove
     * @return array<FullyQualifiedObjectType|AliasedObjectType>
     */
    private function diffFullyQualifiedObjectTypes(array $mainTypes, array $typesToRemove) : array
    {
        foreach ($mainTypes as $key => $mainType) {
            foreach ($typesToRemove as $typeToRemove) {
                if ($mainType->equals($typeToRemove)) {
                    unset($mainTypes[$key]);
                }
            }
        }
        return \array_values($mainTypes);
    }
    /**
     * @param array<AliasedObjectType|FullyQualifiedObjectType> $useImportTypes
     * @param array<FullyQualifiedObjectType|AliasedObjectType> $functionUseImportTypes
     * @return Use_[]
     */
    private function createUses(array $useImportTypes, array $functionUseImportTypes, ?string $namespaceName) : array
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
    private function getNamespaceName(Namespace_ $namespace) : ?string
    {
        if (!$namespace->name instanceof Name) {
            return null;
        }
        return $namespace->name->toString();
    }
    private function isCurrentNamespace(string $namespaceName, ObjectType $objectType) : bool
    {
        $afterCurrentNamespace = Strings::after($objectType->getClassName(), $namespaceName . '\\');
        if ($afterCurrentNamespace === null) {
            return \false;
        }
        return \strpos($afterCurrentNamespace, '\\') === \false;
    }
}
