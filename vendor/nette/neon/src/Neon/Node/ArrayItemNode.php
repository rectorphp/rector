<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220418\Nette\Neon\Node;

use RectorPrefix20220418\Nette\Neon\Node;
/** @internal */
final class ArrayItemNode extends \RectorPrefix20220418\Nette\Neon\Node
{
    /** @var ?Node */
    public $key;
    /** @var Node */
    public $value;
    public function __construct(int $pos = null)
    {
        $this->startPos = $this->endPos = $pos;
    }
    /** @param  self[]  $items */
    public static function itemsToArray(array $items) : array
    {
        $res = [];
        foreach ($items as $item) {
            if ($item->key === null) {
                $res[] = $item->value->toValue();
            } else {
                $res[(string) $item->key->toValue()] = $item->value->toValue();
            }
        }
        return $res;
    }
    /** @param  self[]  $items */
    public static function itemsToInlineString(array $items) : string
    {
        $res = '';
        foreach ($items as $item) {
            $res .= ($res === '' ? '' : ', ') . ($item->key ? $item->key->toString() . ': ' : '') . $item->value->toString();
        }
        return $res;
    }
    /** @param  self[]  $items */
    public static function itemsToBlockString(array $items) : string
    {
        $res = '';
        foreach ($items as $item) {
            $v = $item->value->toString();
            $res .= ($item->key ? $item->key->toString() . ':' : '-') . ($item->value instanceof \RectorPrefix20220418\Nette\Neon\Node\BlockArrayNode && $item->value->items ? "\n" . $v . (\substr($v, -2, 1) === "\n" ? '' : "\n") : ' ' . $v . "\n");
        }
        return $res;
    }
    public function toValue()
    {
        throw new \LogicException();
    }
    public function toString() : string
    {
        throw new \LogicException();
    }
    public function getSubNodes() : array
    {
        return $this->key ? [&$this->key, &$this->value] : [&$this->value];
    }
}
