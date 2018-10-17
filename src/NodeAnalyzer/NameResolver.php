<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;

final class NameResolver
{
    public function resolve(Node $node): ?string
    {
        if ($node instanceof Property) {
            if (! count($node->props)) {
                return null;
            }

            return $this->resolve($node->props[0]);
        }

        if ($node instanceof MethodCall || $node instanceof StaticCall || $node instanceof FuncCall) {
            if ($node->name instanceof Name || $node->name instanceof Identifier) {
                return $node->name->toString();
            }

            if (is_string($node->name)) {
                return $node->name;
            }
        }

        if ($node instanceof PropertyProperty || $node instanceof Variable || $node instanceof ClassMethod || $node instanceof ClassConstFetch || $node instanceof PropertyFetch) {
            // be careful, can be expression!
            return (string) $node->name;
        }

        return null;
    }
}
