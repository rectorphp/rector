<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Identifier;
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
     * @param Stmt[] $stmts
     * @return string[]
     */
    public function resolveFromNode(Node $node, array $stmts) : array
    {
        if (!$node instanceof Namespace_) {
            /** @var Namespace_[] $namespaces */
            $namespaces = \array_filter($stmts, static function (Stmt $stmt) : bool {
                return $stmt instanceof Namespace_;
            });
            foreach ($namespaces as $namespace) {
                $isFoundInNamespace = (bool) $this->betterNodeFinder->findFirst($namespace, static function (Node $subNode) use($node) : bool {
                    return $subNode === $node;
                });
                if ($isFoundInNamespace) {
                    $node = $namespace;
                    break;
                }
            }
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
            if (!$useUse->alias instanceof Identifier) {
                return;
            }
            $aliasedUses[] = $name;
        });
        return $aliasedUses;
    }
}
