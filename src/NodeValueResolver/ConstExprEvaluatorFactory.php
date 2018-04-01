<?php declare(strict_types=1);

namespace Rector\NodeValueResolver;

use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use Rector\Node\Attribute;

final class ConstExprEvaluatorFactory
{
    public function create(): ConstExprEvaluator
    {
        $fallbackEvaluator = function (Expr $expr) {
            if ($expr instanceof ClassConstFetch) {
                return $this->resolveClassConstFetch($expr);
            }
        };

        return new ConstExprEvaluator($fallbackEvaluator);
    }

    private function resolveClassConstFetch(ClassConstFetch $classConstFetchNode): string
    {
        $class = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME)->toString();

        /** @var Identifier $identifierNode */
        $identifierNode = $classConstFetchNode->name;

        $constant = $identifierNode->toString();

        return $class . '::' . $constant;
    }
}
