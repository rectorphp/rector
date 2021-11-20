<?php

declare (strict_types=1);
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
    public function createFromValues(array $values) : array
    {
        $args = [];
        foreach ($values as $key => $argValue) {
            $expr = \PhpParser\BuilderHelpers::normalizeValue($argValue);
            $name = null;
            // for named arguments
            if (\is_string($key)) {
                $name = new \PhpParser\Node\Identifier($key);
            }
            $this->normalizeStringDoubleQuote($expr);
            $args[] = new \PhpParser\Node\Arg($expr, \false, \false, [], $name);
        }
        return $args;
    }
    private function normalizeStringDoubleQuote(\PhpParser\Node\Expr $expr) : void
    {
        if (!$expr instanceof \PhpParser\Node\Scalar\String_) {
            return;
        }
        // avoid escaping quotes + preserve newlines
        if (\strpos($expr->value, "'") === \false) {
            return;
        }
        if (\strpos($expr->value, "\n") !== \false) {
            return;
        }
        $expr->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, \PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED);
    }
}
