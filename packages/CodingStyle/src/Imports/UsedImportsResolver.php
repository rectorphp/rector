<?php declare(strict_types=1);

namespace Rector\CodingStyle\Imports;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class UsedImportsResolver
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var UseImportsTraverser
     */
    private $useImportsTraverser;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NameResolver $nameResolver,
        UseImportsTraverser $useImportsTraverser
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nameResolver = $nameResolver;
        $this->useImportsTraverser = $useImportsTraverser;
    }

    /**
     * @return string[]
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
     * @return string[]
     */
    public function resolveForStmts(array $stmts): array
    {
        $usedImports = [];

        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($stmts, Class_::class);

        // add class itself
        if ($class !== null) {
            $className = $this->nameResolver->getName($class);
            if ($className !== null) {
                $usedImports[] = $className;
            }
        }

        $this->useImportsTraverser->traverserStmts($stmts, function (
            UseUse $useUse,
            string $name
        ) use (&$usedImports): void {
            $usedImports[] = $name;
        });

        return $usedImports;
    }

    /**
     * @param Stmt[] $stmts
     * @return string[]
     */
    public function resolveFunctionImportsForStmts(array $stmts): array
    {
        $usedFunctionImports = [];

        $this->useImportsTraverser->traverserStmts($stmts, function (
            UseUse $useUse,
            string $name
        ) use (&$usedFunctionImports): void {
            $usedFunctionImports[] = $name;
        }, Use_::TYPE_FUNCTION);

        return $usedFunctionImports;
    }

    /**
     * @return string[]
     */
    public function resolveFunctionImportsForNode(Namespace_ $namespace): array
    {
        return $this->resolveFunctionImportsForStmts($namespace->stmts);
    }

    /**
     * @return string[]
     */
    private function resolveForNamespace(Namespace_ $node): array
    {
        return $this->resolveForStmts($node->stmts);
    }
}
