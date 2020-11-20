<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class AliasUsesResolver
{
    /**
     * @var UseImportsTraverser
     */
    private $useImportsTraverser;

    public function __construct(UseImportsTraverser $useImportsTraverser)
    {
        $this->useImportsTraverser = $useImportsTraverser;
    }

    /**
     * @return string[]
     */
    public function resolveForNode(Node $node): array
    {
        if (! $node instanceof Namespace_) {
            $node = $node->getAttribute(AttributeKey::NAMESPACE_NODE);
        }

        if ($node instanceof Namespace_) {
            return $this->resolveForNamespace($node);
        }

        return [];
    }

    /**
     * @return string[]
     */
    private function resolveForNamespace(Namespace_ $namespace): array
    {
        $aliasedUses = [];

        $this->useImportsTraverser->traverserStmts($namespace->stmts, function (
            UseUse $useUse,
            string $name
        ) use (&$aliasedUses): void {
            if ($useUse->alias === null) {
                return;
            }

            $aliasedUses[] = $name;
        });

        return $aliasedUses;
    }
}
