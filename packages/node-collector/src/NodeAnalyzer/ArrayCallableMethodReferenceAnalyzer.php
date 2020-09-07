<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ArrayCallableMethodReferenceAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * Matches array like: "[$this, 'methodName']" → ['ClassName', 'methodName']
     */
    public function match(Array_ $array): ?ArrayCallable
    {
        if (count($array->items) !== 2) {
            return null;
        }

        if ($array->items[0] === null) {
            return null;
        }

        if ($array->items[1] === null) {
            return null;
        }

        // $this, self, static, FQN
        if (! $this->isThisVariable($array->items[0]->value)) {
            return null;
        }

        if (! $array->items[1]->value instanceof String_) {
            return null;
        }

        /** @var String_ $string */
        $string = $array->items[1]->value;

        $methodName = $string->value;
        $className = $array->getAttribute(AttributeKey::CLASS_NAME);

        if ($className === null) {
            return null;
        }

        return new ArrayCallable($className, $methodName);
    }

    private function isThisVariable(Node $node): bool
    {
        // $this
        if ($node instanceof Variable && $this->nodeNameResolver->isName($node, 'this')) {
            return true;
        }

        if ($node instanceof ClassConstFetch) {
            if (! $this->nodeNameResolver->isName($node->name, 'class')) {
                return false;
            }

            // self::class, static::class
            if ($this->nodeNameResolver->isNames($node->class, ['self', 'static'])) {
                return true;
            }

            /** @var string|null $className */
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);

            if ($className === null) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->class, $className);
        }

        return false;
    }
}
