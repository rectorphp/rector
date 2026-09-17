<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use Rector\PhpParser\Node\Value\ValueResolver;
final class SortDirectionResolver
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    /**
     * Extracts a normalized 'asc' or 'desc' string from strings and constants.
     */
    public function resolve(Expr $expr): ?string
    {
        if ($expr instanceof ClassConstFetch && $expr->name instanceof Identifier) {
            $normalized = strtolower($expr->name->toString());
            if (in_array($normalized, ['asc', 'ascending'], \true)) {
                return 'asc';
            }
            if (in_array($normalized, ['desc', 'descending'], \true)) {
                return 'desc';
            }
        }
        $value = $this->valueResolver->getValue($expr);
        if (is_string($value)) {
            $normalized = strtolower($value);
            if ($normalized === 'asc' || $normalized === 'desc') {
                return $normalized;
            }
        }
        return null;
    }
}
