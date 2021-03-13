<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class UsedImportsResolver
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var UseImportsTraverser
     */
    private $useImportsTraverser;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        UseImportsTraverser $useImportsTraverser
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->useImportsTraverser = $useImportsTraverser;
    }

    /**
     * @return FullyQualifiedObjectType[]
     */
    public function resolveForNode(Node $node): array
    {
        $namespace = $node->getAttribute(AttributeKey::NAMESPACE_NODE);
        if ($namespace instanceof Namespace_) {
            return $this->resolveForNamespace($namespace);
        }

        return [];
    }

    /**
     * @param Stmt[] $stmts
     * @return FullyQualifiedObjectType[]
     */
    public function resolveForStmts(array $stmts): array
    {
        $usedImports = [];

        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($stmts, Class_::class);

        // add class itself
        if ($class !== null) {
            $className = $this->nodeNameResolver->getName($class);
            if ($className !== null) {
                $usedImports[] = new FullyQualifiedObjectType($className);
            }
        }

        $this->useImportsTraverser->traverserStmts($stmts, function (
            UseUse $useUse,
            string $name
        ) use (&$usedImports): void {
            $usedImports[] = new FullyQualifiedObjectType($name);
        });

        return $usedImports;
    }

    /**
     * @param Stmt[] $stmts
     * @return FullyQualifiedObjectType[]
     */
    public function resolveFunctionImportsForStmts(array $stmts): array
    {
        $usedFunctionImports = [];

        $this->useImportsTraverser->traverserStmtsForFunctions($stmts, function (
            UseUse $useUse,
            string $name
        ) use (&$usedFunctionImports): void {
            $usedFunctionImports[] = new FullyQualifiedObjectType($name);
        });

        return $usedFunctionImports;
    }

    /**
     * @return FullyQualifiedObjectType[]
     */
    private function resolveForNamespace(Namespace_ $namespace): array
    {
        return $this->resolveForStmts($namespace->stmts);
    }
}
