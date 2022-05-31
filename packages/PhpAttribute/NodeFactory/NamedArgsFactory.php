<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use RectorPrefix20220531\Nette\Utils\Strings;
use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NamedArgsFactory
{
    /**
     * @see https://regex101.com/r/1bJR0J/1
     * @var string
     */
    private const CLASS_CONST_REGEX = '#(?<class>\\w+)::(?<constant>\\w+)#';
    /**
     * @param array<string|int, mixed|Expr> $values
     * @return Arg[]
     */
    public function createFromValues(array $values) : array
    {
        $args = [];
        foreach ($values as $key => $argValue) {
            $expr = \PhpParser\BuilderHelpers::normalizeValue($argValue);
            $this->normalizeArrayWithConstFetchKey($expr);
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
    private function normalizeArrayWithConstFetchKey(\PhpParser\Node\Expr $expr) : void
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        foreach ($expr->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof \PhpParser\Node\Scalar\String_) {
                continue;
            }
            $string = $arrayItem->key;
            $match = \RectorPrefix20220531\Nette\Utils\Strings::match($string->value, self::CLASS_CONST_REGEX);
            if ($match === null) {
                continue;
            }
            /** @var string $class */
            $class = $match['class'];
            /** @var string $constant */
            $constant = $match['constant'];
            $arrayItem->key = new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name($class), $constant);
        }
    }
}
