<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Nette\Neon;

/** @internal */
final class Traverser
{
    public const DontTraverseChildren = 1;
    public const StopTraversal = 2;
    /** @var callable(Node): (Node|int|null)|null */
    private $enter;
    /** @var callable(Node): (Node|int|null)|null */
    private $leave;
    /** @var bool */
    private $stop;
    /**
     * @param  callable(Node): (Node|int|null)|null  $enter
     * @param  callable(Node): (Node|int|null)|null  $leave
     */
    public function traverse(Node $node, ?callable $enter = null, ?callable $leave = null) : Node
    {
        $this->enter = $enter;
        $this->leave = $leave;
        $this->stop = \false;
        return $this->traverseNode($node);
    }
    private function traverseNode(Node $node) : Node
    {
        $children = \true;
        if ($this->enter) {
            $res = ($this->enter)($node);
            if ($res instanceof Node) {
                $node = $res;
            } elseif ($res === self::DontTraverseChildren) {
                $children = \false;
            } elseif ($res === self::StopTraversal) {
                $this->stop = \true;
                $children = \false;
            }
        }
        if ($children) {
            foreach ($node as &$subnode) {
                $subnode = $this->traverseNode($subnode);
                if ($this->stop) {
                    break;
                }
            }
        }
        if (!$this->stop && $this->leave) {
            $res = ($this->leave)($node);
            if ($res instanceof Node) {
                $node = $res;
            } elseif ($res === self::StopTraversal) {
                $this->stop = \true;
            }
        }
        return $node;
    }
}
