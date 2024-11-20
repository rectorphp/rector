<?php

declare (strict_types=1);
namespace PhpParser\Node;

use PhpParser\Node;
use PhpParser\NodeAbstract;
class DeclareItem extends NodeAbstract
{
    /** @var Node\Identifier Key */
    public \PhpParser\Node\Identifier $key;
    /** @var Node\Expr Value */
    public \PhpParser\Node\Expr $value;
    /**
     * Constructs a declare key=>value pair node.
     *
     * @param string|Node\Identifier $key Key
     * @param Node\Expr $value Value
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct($key, Node\Expr $value, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->key = \is_string($key) ? new Node\Identifier($key) : $key;
        $this->value = $value;
    }
    public function getSubNodeNames() : array
    {
        return ['key', 'value'];
    }
    public function getType() : string
    {
        return 'DeclareItem';
    }
}
// @deprecated compatibility alias
\class_alias(\PhpParser\Node\DeclareItem::class, \PhpParser\Node\Stmt\DeclareDeclare::class);
