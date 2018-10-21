<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;

final class NameResolver
{
    public function resolve(Node $node): ?string
    {
        if ($node instanceof ClassConst) {
            if (! count($node->consts)) {
                return null;
            }

            return $this->resolve($node->consts[0]);
        }

        if ($node instanceof Property) {
            if (! count($node->props)) {
                return null;
            }

            return $this->resolve($node->props[0]);
        }

        if (! property_exists($node, 'name')) {
            return null;
        }

        // unable to resolve
        if ($node->name instanceof Expr) {
            return null;
        }

        return (string) $node->name;
    }
}
