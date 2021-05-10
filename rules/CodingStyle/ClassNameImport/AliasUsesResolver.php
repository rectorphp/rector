<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class AliasUsesResolver
{
    /**
     * @var \Rector\CodingStyle\ClassNameImport\UseImportsTraverser
     */
    private $useImportsTraverser;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\CodingStyle\ClassNameImport\UseImportsTraverser $useImportsTraverser, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->useImportsTraverser = $useImportsTraverser;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return string[]
     */
    public function resolveForNode(\PhpParser\Node $node) : array
    {
        if (!$node instanceof \PhpParser\Node\Stmt\Namespace_) {
            $node = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Namespace_::class);
        }
        if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
            return $this->resolveForNamespace($node);
        }
        return [];
    }
    /**
     * @return string[]
     */
    private function resolveForNamespace(\PhpParser\Node\Stmt\Namespace_ $namespace) : array
    {
        $aliasedUses = [];
        $this->useImportsTraverser->traverserStmts($namespace->stmts, function (\PhpParser\Node\Stmt\UseUse $useUse, string $name) use(&$aliasedUses) : void {
            if ($useUse->alias === null) {
                return;
            }
            $aliasedUses[] = $name;
        });
        return $aliasedUses;
    }
}
