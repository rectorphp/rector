<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Nette\Neon;

/**
 * Converts value to NEON format.
 * @internal
 */
final class Encoder
{
    /** @deprecated */
    public const BLOCK = \true;
    /**
     * @var bool
     */
    public $blockMode = \false;
    /**
     * @var string
     */
    public $indentation = "\t";
    /**
     * Returns the NEON representation of a value.
     * @param mixed $val
     */
    public function encode($val) : string
    {
        $node = $this->valueToNode($val, $this->blockMode);
        return $node->toString();
    }
    /**
     * @param mixed $val
     */
    public function valueToNode($val, bool $blockMode = \false) : Node
    {
        if ($val instanceof \DateTimeInterface) {
            return new Node\LiteralNode($val);
        } elseif ($val instanceof Entity && $val->value === Neon::Chain) {
            $node = new Node\EntityChainNode();
            foreach ($val->attributes as $entity) {
                $node->chain[] = $this->valueToNode($entity);
            }
            return $node;
        } elseif ($val instanceof Entity) {
            return new Node\EntityNode($this->valueToNode($val->value), $this->arrayToNodes($val->attributes));
        } elseif (\is_object($val) || \is_array($val)) {
            if ($blockMode) {
                $node = new Node\BlockArrayNode();
            } else {
                $isList = \is_array($val) && (!$val || \array_keys($val) === \range(0, \count($val) - 1));
                $node = new Node\InlineArrayNode($isList ? '[' : '{');
            }
            $node->items = $this->arrayToNodes($val, $blockMode);
            return $node;
        } elseif (\is_string($val) && Lexer::requiresDelimiters($val)) {
            return new Node\StringNode($val);
        } else {
            return new Node\LiteralNode($val);
        }
    }
    /** @return Node\ArrayItemNode[]
     * @param mixed $val */
    private function arrayToNodes($val, bool $blockMode = \false) : array
    {
        $res = [];
        $counter = 0;
        $hide = \true;
        foreach ($val as $k => $v) {
            $res[] = $item = new Node\ArrayItemNode();
            $item->key = $hide && $k === $counter ? null : self::valueToNode($k);
            $item->value = self::valueToNode($v, $blockMode);
            if ($item->value instanceof Node\BlockArrayNode) {
                $item->value->indentation = $this->indentation;
            }
            if ($hide && \is_int($k)) {
                $hide = $k === $counter;
                $counter = \max($k + 1, $counter);
            }
        }
        return $res;
    }
}
