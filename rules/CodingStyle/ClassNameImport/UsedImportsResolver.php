<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UsedImportsResolver
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\CodingStyle\ClassNameImport\UseImportsTraverser
     */
    private $useImportsTraverser;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\CodingStyle\ClassNameImport\UseImportsTraverser $useImportsTraverser)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportsTraverser = $useImportsTraverser;
    }
    /**
     * @return array<FullyQualifiedObjectType|AliasedObjectType>
     */
    public function resolveForNode(\PhpParser\Node $node) : array
    {
        if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
            $namespace = $node;
        } else {
            $namespace = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Namespace_::class);
        }
        if ($namespace instanceof \PhpParser\Node\Stmt\Namespace_) {
            return $this->resolveForNamespace($namespace);
        }
        return [];
    }
    /**
     * @param Stmt[] $stmts
     * @return array<FullyQualifiedObjectType|AliasedObjectType>
     */
    public function resolveForStmts(array $stmts) : array
    {
        $usedImports = [];
        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($stmts, \PhpParser\Node\Stmt\Class_::class);
        // add class itself
        if ($class !== null) {
            $className = $class->namespacedName->toString();
            $usedImports[] = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($className);
        }
        $this->useImportsTraverser->traverserStmts($stmts, function (\PhpParser\Node\Stmt\UseUse $useUse, string $name) use(&$usedImports) : void {
            if ($useUse->alias !== null) {
                $usedImports[] = new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType($useUse->alias->toString(), $name);
            } else {
                $usedImports[] = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($name);
            }
        });
        return $usedImports;
    }
    /**
     * @param Stmt[] $stmts
     * @return FullyQualifiedObjectType[]
     */
    public function resolveFunctionImportsForStmts(array $stmts) : array
    {
        $usedFunctionImports = [];
        $this->useImportsTraverser->traverserStmtsForFunctions($stmts, function (\PhpParser\Node\Stmt\UseUse $useUse, string $name) use(&$usedFunctionImports) : void {
            $usedFunctionImports[] = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($name);
        });
        return $usedFunctionImports;
    }
    /**
     * @return array<FullyQualifiedObjectType|AliasedObjectType>
     */
    private function resolveForNamespace(\PhpParser\Node\Stmt\Namespace_ $namespace) : array
    {
        return $this->resolveForStmts($namespace->stmts);
    }
}
