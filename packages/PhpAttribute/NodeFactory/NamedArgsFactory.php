<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\NodeFactory;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NamedArgsFactory
{
    /**
     * @param array<string|int, mixed|Expr> $values
     * @return Arg[]
     */
    public function createFromValues(array $values): array
    {
        $args = [];

        foreach ($values as $key => $argValue) {
            $expr = BuilderHelpers::normalizeValue($argValue);
            $name = null;

            // for named arguments
            if (is_string($key)) {
                $name = new Identifier($key);
            }

            $this->normalizeStringDoubleQuote($expr);

            $args[] = new Arg($expr, false, false, [], $name);
        }

        return $args;
    }

    private function normalizeStringDoubleQuote(Expr $expr): void
    {
        if (! $expr instanceof String_) {
            return;
        }

        // avoid escaping quotes + preserve newlines
        if (! str_contains($expr->value, "'")) {
            return;
        }

        if (str_contains($expr->value, "\n")) {
            return;
        }

        $expr->setAttribute(AttributeKey::KIND, String_::KIND_DOUBLE_QUOTED);
    }
}
