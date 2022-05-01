<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220501\Nette\Neon;

/** @internal */
final class Parser
{
    /** @var TokenStream */
    private $tokens;
    /** @var int[] */
    private $posToLine = [];
    public function parse(\RectorPrefix20220501\Nette\Neon\TokenStream $tokens) : \RectorPrefix20220501\Nette\Neon\Node
    {
        $this->tokens = $tokens;
        $this->initLines();
        while ($this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::Newline)) {
        }
        $node = $this->parseBlock($this->tokens->getIndentation());
        while ($this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::Newline)) {
        }
        if ($this->tokens->isNext()) {
            $this->tokens->error();
        }
        return $node;
    }
    private function parseBlock(string $indent, bool $onlyBullets = \false) : \RectorPrefix20220501\Nette\Neon\Node
    {
        $res = new \RectorPrefix20220501\Nette\Neon\Node\BlockArrayNode($indent);
        $this->injectPos($res);
        $keyCheck = [];
        loop:
        $item = new \RectorPrefix20220501\Nette\Neon\Node\ArrayItemNode();
        $this->injectPos($item);
        if ($this->tokens->consume('-')) {
            // continue
        } elseif (!$this->tokens->isNext() || $onlyBullets) {
            return $res->items ? $res : $this->injectPos(new \RectorPrefix20220501\Nette\Neon\Node\LiteralNode(null));
        } else {
            $value = $this->parseValue();
            if ($this->tokens->consume(':', '=')) {
                $this->checkArrayKey($value, $keyCheck);
                $item->key = $value;
            } else {
                if ($res->items) {
                    $this->tokens->error();
                }
                return $value;
            }
        }
        $res->items[] = $item;
        $item->value = new \RectorPrefix20220501\Nette\Neon\Node\LiteralNode(null);
        $this->injectPos($item->value);
        if ($this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::Newline)) {
            while ($this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::Newline)) {
            }
            $nextIndent = $this->tokens->getIndentation();
            if (\strncmp($nextIndent, $indent, \min(\strlen($nextIndent), \strlen($indent)))) {
                $this->tokens->error('Invalid combination of tabs and spaces');
            } elseif (\strlen($nextIndent) > \strlen($indent)) {
                // open new block
                $item->value = $this->parseBlock($nextIndent);
            } elseif (\strlen($nextIndent) < \strlen($indent)) {
                // close block
                return $res;
            } elseif ($item->key !== null && $this->tokens->isNext('-')) {
                // special dash subblock
                $item->value = $this->parseBlock($indent, \true);
            }
        } elseif ($item->key === null) {
            $item->value = $this->parseBlock($indent . '  ');
            // open new block after dash
        } elseif ($this->tokens->isNext()) {
            $item->value = $this->parseValue();
            if ($this->tokens->isNext() && !$this->tokens->isNext(\RectorPrefix20220501\Nette\Neon\Token::Newline)) {
                $this->tokens->error();
            }
        }
        if ($item->value instanceof \RectorPrefix20220501\Nette\Neon\Node\BlockArrayNode) {
            $item->value->indentation = \substr($item->value->indentation, \strlen($indent));
        }
        $this->injectPos($res, $res->startTokenPos, $item->value->endTokenPos);
        $this->injectPos($item, $item->startTokenPos, $item->value->endTokenPos);
        while ($this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::Newline)) {
        }
        if (!$this->tokens->isNext()) {
            return $res;
        }
        $nextIndent = $this->tokens->getIndentation();
        if (\strncmp($nextIndent, $indent, \min(\strlen($nextIndent), \strlen($indent)))) {
            $this->tokens->error('Invalid combination of tabs and spaces');
        } elseif (\strlen($nextIndent) > \strlen($indent)) {
            $this->tokens->error('Bad indentation');
        } elseif (\strlen($nextIndent) < \strlen($indent)) {
            // close block
            return $res;
        }
        goto loop;
    }
    private function parseValue() : \RectorPrefix20220501\Nette\Neon\Node
    {
        if ($token = $this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::String)) {
            try {
                $node = new \RectorPrefix20220501\Nette\Neon\Node\StringNode(\RectorPrefix20220501\Nette\Neon\Node\StringNode::parse($token->value));
                $this->injectPos($node, $this->tokens->getPos() - 1);
            } catch (\RectorPrefix20220501\Nette\Neon\Exception $e) {
                $this->tokens->error($e->getMessage(), $this->tokens->getPos() - 1);
            }
        } elseif ($token = $this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::Literal)) {
            $pos = $this->tokens->getPos() - 1;
            $node = new \RectorPrefix20220501\Nette\Neon\Node\LiteralNode(\RectorPrefix20220501\Nette\Neon\Node\LiteralNode::parse($token->value, $this->tokens->isNext(':', '=')));
            $this->injectPos($node, $pos);
        } elseif ($this->tokens->isNext('[', '(', '{')) {
            $node = $this->parseBraces();
        } else {
            $this->tokens->error();
        }
        return $this->parseEntity($node);
    }
    private function parseEntity(\RectorPrefix20220501\Nette\Neon\Node $node) : \RectorPrefix20220501\Nette\Neon\Node
    {
        if (!$this->tokens->isNext('(')) {
            return $node;
        }
        $attributes = $this->parseBraces();
        $entities[] = $this->injectPos(new \RectorPrefix20220501\Nette\Neon\Node\EntityNode($node, $attributes->items), $node->startTokenPos, $attributes->endTokenPos);
        while ($token = $this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::Literal)) {
            $valueNode = new \RectorPrefix20220501\Nette\Neon\Node\LiteralNode(\RectorPrefix20220501\Nette\Neon\Node\LiteralNode::parse($token->value));
            $this->injectPos($valueNode, $this->tokens->getPos() - 1);
            if ($this->tokens->isNext('(')) {
                $attributes = $this->parseBraces();
                $entities[] = $this->injectPos(new \RectorPrefix20220501\Nette\Neon\Node\EntityNode($valueNode, $attributes->items), $valueNode->startTokenPos, $attributes->endTokenPos);
            } else {
                $entities[] = $this->injectPos(new \RectorPrefix20220501\Nette\Neon\Node\EntityNode($valueNode), $valueNode->startTokenPos);
                break;
            }
        }
        return \count($entities) === 1 ? $entities[0] : $this->injectPos(new \RectorPrefix20220501\Nette\Neon\Node\EntityChainNode($entities), $node->startTokenPos, \end($entities)->endTokenPos);
    }
    private function parseBraces() : \RectorPrefix20220501\Nette\Neon\Node\InlineArrayNode
    {
        $token = $this->tokens->consume();
        $endBrace = ['[' => ']', '{' => '}', '(' => ')'][$token->value];
        $res = new \RectorPrefix20220501\Nette\Neon\Node\InlineArrayNode($token->value);
        $this->injectPos($res, $this->tokens->getPos() - 1);
        $keyCheck = [];
        loop:
        while ($this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::Newline)) {
        }
        if ($this->tokens->consume($endBrace)) {
            $this->injectPos($res, $res->startTokenPos, $this->tokens->getPos() - 1);
            return $res;
        }
        $res->items[] = $item = new \RectorPrefix20220501\Nette\Neon\Node\ArrayItemNode();
        $this->injectPos($item, $this->tokens->getPos());
        $value = $this->parseValue();
        if ($this->tokens->consume(':', '=')) {
            $this->checkArrayKey($value, $keyCheck);
            $item->key = $value;
            $item->value = $this->tokens->isNext(\RectorPrefix20220501\Nette\Neon\Token::Newline, ',', $endBrace) ? $this->injectPos(new \RectorPrefix20220501\Nette\Neon\Node\LiteralNode(null), $this->tokens->getPos()) : $this->parseValue();
        } else {
            $item->value = $value;
        }
        $this->injectPos($item, $item->startTokenPos, $item->value->endTokenPos);
        if ($this->tokens->consume(',', \RectorPrefix20220501\Nette\Neon\Token::Newline)) {
            goto loop;
        }
        while ($this->tokens->consume(\RectorPrefix20220501\Nette\Neon\Token::Newline)) {
        }
        if (!$this->tokens->isNext($endBrace)) {
            $this->tokens->error();
        }
        goto loop;
    }
    /** @param  true[]  $arr */
    private function checkArrayKey(\RectorPrefix20220501\Nette\Neon\Node $key, array &$arr) : void
    {
        if (!$key instanceof \RectorPrefix20220501\Nette\Neon\Node\StringNode && !$key instanceof \RectorPrefix20220501\Nette\Neon\Node\LiteralNode || !\is_scalar($key->value)) {
            $this->tokens->error('Unacceptable key', $key->startTokenPos);
        }
        $k = (string) $key->value;
        if (\array_key_exists($k, $arr)) {
            $this->tokens->error("Duplicated key '{$k}'", $key->startTokenPos);
        }
        $arr[$k] = \true;
    }
    private function injectPos(\RectorPrefix20220501\Nette\Neon\Node $node, int $start = null, int $end = null) : \RectorPrefix20220501\Nette\Neon\Node
    {
        $node->startTokenPos = $start ?? $this->tokens->getPos();
        $node->startLine = $this->posToLine[$node->startTokenPos];
        $node->endTokenPos = $end ?? $node->startTokenPos;
        $node->endLine = $this->posToLine[$node->endTokenPos + 1] ?? \end($this->posToLine);
        return $node;
    }
    private function initLines() : void
    {
        $this->posToLine = [];
        $line = 1;
        foreach ($this->tokens->getTokens() as $token) {
            $this->posToLine[] = $line;
            $line += \substr_count($token->value, "\n");
        }
        $this->posToLine[] = $line;
    }
}
