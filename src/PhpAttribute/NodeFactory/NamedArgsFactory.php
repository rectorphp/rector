<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NamedArgsFactory
{
    /**
     * @param array<string|int, mixed|Expr> $values
     * @return list<Arg>
     */
    public function createFromValues(array $values) : array
    {
        $args = [];
        foreach ($values as $key => $argValue) {
            $name = null;
            if ($argValue instanceof ArrayItem) {
                if ($argValue->key instanceof String_) {
                    $name = new Identifier($argValue->key->value);
                }
                $argValue = $argValue->value;
            }
            $expr = BuilderHelpers::normalizeValue($argValue);
            // for named arguments
            if (!$name instanceof Identifier && \is_string($key)) {
                $name = new Identifier($key);
            }
            $this->normalizeStringDoubleQuote($expr);
            $args[] = new Arg($expr, \false, \false, [], $name);
        }
        return $args;
    }
    private function normalizeStringDoubleQuote(Expr $expr) : void
    {
        if (!$expr instanceof String_) {
            return;
        }
        // avoid escaping quotes + preserve newlines
        if (\strpos($expr->value, "'") === \false) {
            return;
        }
        if (\strpos($expr->value, "\n") !== \false) {
            return;
        }
        $expr->setAttribute(AttributeKey::KIND, String_::KIND_DOUBLE_QUOTED);
    }
}
