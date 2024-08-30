<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class AliasUsesResolver
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\UseImportsTraverser
     */
    private $useImportsTraverser;
    public function __construct(\Rector\CodingStyle\ClassNameImport\UseImportsTraverser $useImportsTraverser)
    {
        $this->useImportsTraverser = $useImportsTraverser;
    }
    /**
     * @param Stmt[] $stmts
     * @return string[]
     */
    public function resolveFromNode(Node $node, array $stmts) : array
    {
        if (!$node instanceof Namespace_ && !$node instanceof FileWithoutNamespace) {
            /** @var Namespace_[]|FileWithoutNamespace[] $namespaces */
            $namespaces = \array_filter($stmts, static function (Stmt $stmt) : bool {
                return $stmt instanceof Namespace_ || $stmt instanceof FileWithoutNamespace;
            });
            if (\count($namespaces) !== 1) {
                return [];
            }
            $node = \current($namespaces);
        }
        return $this->resolveFromStmts($node->stmts);
    }
    /**
     * @param Stmt[] $stmts
     * @return string[]
     */
    public function resolveFromStmts(array $stmts) : array
    {
        $aliasedUses = [];
        /** @param Use_::TYPE_* $useType */
        $this->useImportsTraverser->traverserStmts($stmts, static function (int $useType, UseUse $useUse, string $name) use(&$aliasedUses) : void {
            if ($useType !== Use_::TYPE_NORMAL) {
                return;
            }
            if (!$useUse->alias instanceof Identifier) {
                return;
            }
            $aliasedUses[] = $name;
        });
        return $aliasedUses;
    }
}
