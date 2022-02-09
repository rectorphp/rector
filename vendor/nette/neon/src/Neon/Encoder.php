<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220209\Nette\Neon;

/**
 * Converts value to NEON format.
 * @internal
 */
final class Encoder
{
    /** @deprecated */
    public const BLOCK = \true;
    /** @var bool */
    public $blockMode = \false;
    /** @var string */
    public $indentation = "\t";
    /**
     * Returns the NEON representation of a value.
     */
    public function encode($val) : string
    {
        $node = $this->valueToNode($val, $this->blockMode);
        return $node->toString();
    }
    public function valueToNode($val, bool $blockMode = \false) : \RectorPrefix20220209\Nette\Neon\Node
    {
        if ($val instanceof \DateTimeInterface) {
            return new \RectorPrefix20220209\Nette\Neon\Node\LiteralNode($val);
        } elseif ($val instanceof \RectorPrefix20220209\Nette\Neon\Entity && $val->value === \RectorPrefix20220209\Nette\Neon\Neon::CHAIN) {
            $node = new \RectorPrefix20220209\Nette\Neon\Node\EntityChainNode();
            foreach ($val->attributes as $entity) {
                $node->chain[] = $this->valueToNode($entity);
            }
            return $node;
        } elseif ($val instanceof \RectorPrefix20220209\Nette\Neon\Entity) {
            return new \RectorPrefix20220209\Nette\Neon\Node\EntityNode($this->valueToNode($val->value), $this->arrayToNodes((array) $val->attributes));
        } elseif (\is_object($val) || \is_array($val)) {
            if ($blockMode) {
                $node = new \RectorPrefix20220209\Nette\Neon\Node\BlockArrayNode();
            } else {
                $isList = \is_array($val) && (!$val || \array_keys($val) === \range(0, \count($val) - 1));
                $node = new \RectorPrefix20220209\Nette\Neon\Node\InlineArrayNode($isList ? '[' : '{');
            }
            $node->items = $this->arrayToNodes($val, $blockMode);
            return $node;
        } elseif (\is_string($val) && \RectorPrefix20220209\Nette\Neon\Lexer::requiresDelimiters($val)) {
            return new \RectorPrefix20220209\Nette\Neon\Node\StringNode($val);
        } else {
            return new \RectorPrefix20220209\Nette\Neon\Node\LiteralNode($val);
        }
    }
    private function arrayToNodes($val, bool $blockMode = \false) : array
    {
        $res = [];
        $counter = 0;
        $hide = \true;
        foreach ($val as $k => $v) {
            $res[] = $item = new \RectorPrefix20220209\Nette\Neon\Node\ArrayItemNode();
            $item->key = $hide && $k === $counter ? null : self::valueToNode($k);
            $item->value = self::valueToNode($v, $blockMode);
            if ($item->value instanceof \RectorPrefix20220209\Nette\Neon\Node\BlockArrayNode) {
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
