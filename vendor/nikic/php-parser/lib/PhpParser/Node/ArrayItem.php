<?php

declare (strict_types=1);
namespace PhpParser\Node;

use PhpParser\NodeAbstract;
class ArrayItem extends NodeAbstract
{
    /** @var null|Expr Key */
    public ?\PhpParser\Node\Expr $key;
    /** @var Expr Value */
    public \PhpParser\Node\Expr $value;
    /** @var bool Whether to assign by reference */
    public bool $byRef;
    /** @var bool Whether to unpack the argument */
    public bool $unpack;
    /**
     * Constructs an array item node.
     *
     * @param Expr $value Value
     * @param null|Expr $key Key
     * @param bool $byRef Whether to assign by reference
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(\PhpParser\Node\Expr $value, ?\PhpParser\Node\Expr $key = null, bool $byRef = \false, array $attributes = [], bool $unpack = \false)
    {
        $this->attributes = $attributes;
        $this->key = $key;
        $this->value = $value;
        $this->byRef = $byRef;
        $this->unpack = $unpack;
    }
    public function getSubNodeNames() : array
    {
        return ['key', 'value', 'byRef', 'unpack'];
    }
    public function getType() : string
    {
        return 'ArrayItem';
    }
}
// @deprecated compatibility alias
\class_alias(\PhpParser\Node\ArrayItem::class, \PhpParser\Node\Expr\ArrayItem::class);
