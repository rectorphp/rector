<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeAnalyzer;

use PhpParser\Node\Expr;
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
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Matches array like: "[$this, 'methodName']" → ['ClassName', 'methodName']
     */
    public function match(\PhpParser\Node\Expr\Array_ $array) : ?\Rector\NodeCollector\ValueObject\ArrayCallable
    {
        $arrayItems = $array->items;
        if (\count($arrayItems) !== 2) {
            return null;
        }
        if ($array->items[0] === null) {
            return null;
        }
        if ($array->items[1] === null) {
            return null;
        }
        // $this, self, static, FQN
        if (!$this->isThisVariable($array->items[0]->value)) {
            return null;
        }
        if (!$array->items[1]->value instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        /** @var String_ $string */
        $string = $array->items[1]->value;
        $methodName = $string->value;
        $className = $array->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }
        return new \Rector\NodeCollector\ValueObject\ArrayCallable($className, $methodName);
    }
    private function isThisVariable(\PhpParser\Node\Expr $expr) : bool
    {
        // $this
        if ($expr instanceof \PhpParser\Node\Expr\Variable && $this->nodeNameResolver->isName($expr, 'this')) {
            return \true;
        }
        if ($expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            if (!$this->nodeNameResolver->isName($expr->name, 'class')) {
                return \false;
            }
            // self::class, static::class
            if ($this->nodeNameResolver->isNames($expr->class, ['self', 'static'])) {
                return \true;
            }
            /** @var string|null $className */
            $className = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            if ($className === null) {
                return \false;
            }
            return $this->nodeNameResolver->isName($expr->class, $className);
        }
        return \false;
    }
}
