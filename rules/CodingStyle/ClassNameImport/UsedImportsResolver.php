<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UsedImportsResolver
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\UseImportsTraverser
     */
    private $useImportsTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(BetterNodeFinder $betterNodeFinder, \Rector\CodingStyle\ClassNameImport\UseImportsTraverser $useImportsTraverser, NodeNameResolver $nodeNameResolver, CurrentFileProvider $currentFileProvider)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportsTraverser = $useImportsTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->currentFileProvider = $currentFileProvider;
    }
    private function resolveCurrentNamespaceForNode(Node $node) : ?Namespace_
    {
        if ($node instanceof Namespace_) {
            return $node;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        $stmts = $file->getNewStmts();
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                $foundNode = $this->betterNodeFinder->findFirst($stmt, static function (Node $subNode) use($node) : bool {
                    return $subNode === $node;
                });
                if ($foundNode instanceof Node) {
                    return $stmt;
                }
            }
        }
        return null;
    }
    /**
     * @return array<FullyQualifiedObjectType|AliasedObjectType>
     */
    public function resolveForNode(Node $node) : array
    {
        $namespace = $this->resolveCurrentNamespaceForNode($node);
        if ($namespace instanceof Namespace_) {
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
        $class = $this->betterNodeFinder->findFirstInstanceOf($stmts, Class_::class);
        // add class itself
        // is not anonymous class
        if ($class instanceof Class_) {
            $className = (string) $this->nodeNameResolver->getName($class);
            $usedImports[] = new FullyQualifiedObjectType($className);
        }
        $this->useImportsTraverser->traverserStmts($stmts, static function (UseUse $useUse, string $name) use(&$usedImports) : void {
            if ($useUse->alias instanceof Identifier) {
                $usedImports[] = new AliasedObjectType($useUse->alias->toString(), $name);
            } else {
                $usedImports[] = new FullyQualifiedObjectType($name);
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
        $this->useImportsTraverser->traverserStmtsForFunctions($stmts, static function (UseUse $useUse, string $name) use(&$usedFunctionImports) : void {
            $usedFunctionImports[] = new FullyQualifiedObjectType($name);
        });
        return $usedFunctionImports;
    }
    /**
     * @return array<FullyQualifiedObjectType|AliasedObjectType>
     */
    private function resolveForNamespace(Namespace_ $namespace) : array
    {
        return $this->resolveForStmts($namespace->stmts);
    }
}
