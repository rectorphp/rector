<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class AliasUsesResolver
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\UseImportsTraverser
     */
    private $useImportsTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\CodingStyle\ClassNameImport\UseImportsTraverser $useImportsTraverser, BetterNodeFinder $betterNodeFinder)
    {
        $this->useImportsTraverser = $useImportsTraverser;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return string[]
     */
    public function resolveFromNode(Node $node) : array
    {
        if (!$node instanceof Namespace_) {
            $node = $this->betterNodeFinder->findParentType($node, Namespace_::class);
        }
        if ($node instanceof Namespace_) {
            return $this->resolveFromStmts($node->stmts);
        }
        return [];
    }
    /**
     * @param Stmt[] $stmts
     * @return string[]
     */
    public function resolveFromStmts(array $stmts) : array
    {
        $aliasedUses = [];
        $this->useImportsTraverser->traverserStmts($stmts, static function (UseUse $useUse, string $name) use(&$aliasedUses) : void {
            if ($useUse->alias === null) {
                return;
            }
            $aliasedUses[] = $name;
        });
        return $aliasedUses;
    }
}
