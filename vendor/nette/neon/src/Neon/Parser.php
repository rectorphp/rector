<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Neon;

/** @internal */
final class Parser
{
    private const PATTERN_DATETIME = '#\\d\\d\\d\\d-\\d\\d?-\\d\\d?(?:(?:[Tt]| ++)\\d\\d?:\\d\\d:\\d\\d(?:\\.\\d*+)? *+(?:Z|[-+]\\d\\d?(?::?\\d\\d)?)?)?$#DA';
    private const PATTERN_HEX = '#0x[0-9a-fA-F]++$#DA';
    private const PATTERN_OCTAL = '#0o[0-7]++$#DA';
    private const PATTERN_BINARY = '#0b[0-1]++$#DA';
    private const SIMPLE_TYPES = ['true' => \true, 'True' => \true, 'TRUE' => \true, 'yes' => \true, 'Yes' => \true, 'YES' => \true, 'on' => \true, 'On' => \true, 'ON' => \true, 'false' => \false, 'False' => \false, 'FALSE' => \false, 'no' => \false, 'No' => \false, 'NO' => \false, 'off' => \false, 'Off' => \false, 'OFF' => \false, 'null' => null, 'Null' => null, 'NULL' => null];
    private const DEPRECATED_TYPES = ['on' => 1, 'On' => 1, 'ON' => 1, 'off' => 1, 'Off' => 1, 'OFF' => 1];
    private const ESCAPE_SEQUENCES = ['t' => "\t", 'n' => "\n", 'r' => "\r", 'f' => "\f", 'b' => "\10", '"' => '"', '\\' => '\\', '/' => '/', '_' => " "];
    /** @var TokenStream */
    private $tokens;
    public function parse(\RectorPrefix20211020\Nette\Neon\TokenStream $tokens) : \RectorPrefix20211020\Nette\Neon\Node
    {
        $this->tokens = $tokens;
        while ($this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::NEWLINE)) {
        }
        $node = $this->parseBlock($this->tokens->getIndentation());
        while ($this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::NEWLINE)) {
        }
        if ($this->tokens->isNext()) {
            $this->tokens->error();
        }
        return $node;
    }
    private function parseBlock(string $indent, bool $onlyBullets = \false) : \RectorPrefix20211020\Nette\Neon\Node
    {
        $res = new \RectorPrefix20211020\Nette\Neon\Node\ArrayNode($indent, $this->tokens->getPos());
        $keyCheck = [];
        loop:
        $item = new \RectorPrefix20211020\Nette\Neon\Node\ArrayItemNode($this->tokens->getPos());
        if ($this->tokens->consume('-')) {
            // continue
        } elseif (!$this->tokens->isNext() || $onlyBullets) {
            return $res->items ? $res : new \RectorPrefix20211020\Nette\Neon\Node\LiteralNode(null, $this->tokens->getPos());
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
        $item->value = new \RectorPrefix20211020\Nette\Neon\Node\LiteralNode(null, $this->tokens->getPos());
        if ($this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::NEWLINE)) {
            while ($this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::NEWLINE)) {
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
            if ($this->tokens->isNext() && !$this->tokens->isNext(\RectorPrefix20211020\Nette\Neon\Token::NEWLINE)) {
                $this->tokens->error();
            }
        }
        $res->endPos = $item->endPos = $item->value->endPos;
        while ($this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::NEWLINE)) {
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
    private function parseValue() : \RectorPrefix20211020\Nette\Neon\Node
    {
        if ($token = $this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::STRING)) {
            $node = new \RectorPrefix20211020\Nette\Neon\Node\StringNode($this->decodeString($token->value), $this->tokens->getPos() - 1);
        } elseif ($token = $this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::LITERAL)) {
            $pos = $this->tokens->getPos() - 1;
            $node = new \RectorPrefix20211020\Nette\Neon\Node\LiteralNode($this->literalToValue($token->value, $this->tokens->isNext(':', '=')), $pos);
        } elseif ($this->tokens->isNext('[', '(', '{')) {
            $node = $this->parseBraces();
        } else {
            $this->tokens->error();
        }
        return $this->parseEntity($node);
    }
    private function parseEntity(\RectorPrefix20211020\Nette\Neon\Node $node) : \RectorPrefix20211020\Nette\Neon\Node
    {
        if (!$this->tokens->isNext('(')) {
            return $node;
        }
        $attributes = $this->parseBraces();
        $entities[] = new \RectorPrefix20211020\Nette\Neon\Node\EntityNode($node, $attributes->items, $node->startPos, $attributes->endPos);
        while ($token = $this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::LITERAL)) {
            $valueNode = new \RectorPrefix20211020\Nette\Neon\Node\LiteralNode($this->literalToValue($token->value), $this->tokens->getPos() - 1);
            if ($this->tokens->isNext('(')) {
                $attributes = $this->parseBraces();
                $entities[] = new \RectorPrefix20211020\Nette\Neon\Node\EntityNode($valueNode, $attributes->items, $valueNode->startPos, $attributes->endPos);
            } else {
                $entities[] = new \RectorPrefix20211020\Nette\Neon\Node\EntityNode($valueNode, [], $valueNode->startPos);
                break;
            }
        }
        return \count($entities) === 1 ? $entities[0] : new \RectorPrefix20211020\Nette\Neon\Node\EntityChainNode($entities, $node->startPos, \end($entities)->endPos);
    }
    private function parseBraces() : \RectorPrefix20211020\Nette\Neon\Node\ArrayNode
    {
        $token = $this->tokens->consume();
        $endBrace = ['[' => ']', '{' => '}', '(' => ')'][$token->value];
        $res = new \RectorPrefix20211020\Nette\Neon\Node\ArrayNode(null, $this->tokens->getPos() - 1);
        $keyCheck = [];
        loop:
        while ($this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::NEWLINE)) {
        }
        if ($this->tokens->consume($endBrace)) {
            $res->endPos = $this->tokens->getPos() - 1;
            return $res;
        }
        $res->items[] = $item = new \RectorPrefix20211020\Nette\Neon\Node\ArrayItemNode($this->tokens->getPos());
        $value = $this->parseValue();
        if ($this->tokens->consume(':', '=')) {
            $this->checkArrayKey($value, $keyCheck);
            $item->key = $value;
            $item->value = $this->tokens->isNext(\RectorPrefix20211020\Nette\Neon\Token::NEWLINE, ',', $endBrace) ? new \RectorPrefix20211020\Nette\Neon\Node\LiteralNode(null, $this->tokens->getPos()) : $this->parseValue();
        } else {
            $item->value = $value;
        }
        $item->endPos = $item->value->endPos;
        if ($this->tokens->consume(',', \RectorPrefix20211020\Nette\Neon\Token::NEWLINE)) {
            goto loop;
        }
        while ($this->tokens->consume(\RectorPrefix20211020\Nette\Neon\Token::NEWLINE)) {
        }
        if (!$this->tokens->isNext($endBrace)) {
            $this->tokens->error();
        }
        goto loop;
    }
    private function decodeString(string $s) : string
    {
        if (\preg_match('#^...\\n++([\\t ]*+)#', $s, $m)) {
            // multiline
            $res = \substr($s, 3, -3);
            $res = \str_replace("\n" . $m[1], "\n", $res);
            $res = \preg_replace('#^\\n|\\n[\\t ]*+$#D', '', $res);
        } else {
            $res = \substr($s, 1, -1);
            if ($s[0] === "'") {
                $res = \str_replace("''", "'", $res);
            }
        }
        if ($s[0] === '"') {
            $res = \preg_replace_callback('#\\\\(?:ud[89ab][0-9a-f]{2}\\\\ud[c-f][0-9a-f]{2}|u[0-9a-f]{4}|x[0-9a-f]{2}|.)#i', function (array $m) : string {
                $sq = $m[0];
                if (isset(self::ESCAPE_SEQUENCES[$sq[1]])) {
                    return self::ESCAPE_SEQUENCES[$sq[1]];
                } elseif ($sq[1] === 'u' && \strlen($sq) >= 6) {
                    return $this->decodeUnicodeSequence($sq);
                } elseif ($sq[1] === 'x' && \strlen($sq) === 4) {
                    \trigger_error("Neon: '{$sq}' is deprecated, use '\\uXXXX' instead.", \E_USER_DEPRECATED);
                    return \chr(\hexdec(\substr($sq, 2)));
                } else {
                    $this->tokens->error("Invalid escaping sequence {$sq}", $this->tokens->getPos() - 1);
                }
            }, $res);
        }
        return $res;
    }
    private function decodeUnicodeSequence(string $sq) : string
    {
        $lead = \hexdec(\substr($sq, 2, 4));
        $tail = \hexdec(\substr($sq, 8, 4));
        $code = $tail ? 0x2400 + ($lead - 0xd800 << 10) + $tail : $lead;
        if ($code >= 0xd800 && $code <= 0xdfff) {
            $this->tokens->error("Invalid UTF-8 (lone surrogate) {$sq}", $this->tokens->getPos() - 1);
        }
        return \function_exists('iconv') ? \iconv('UTF-32BE', 'UTF-8//IGNORE', \pack('N', $code)) : \mb_convert_encoding(\pack('N', $code), 'UTF-8', 'UTF-32BE');
    }
    private function checkArrayKey(\RectorPrefix20211020\Nette\Neon\Node $key, array &$arr) : void
    {
        if (!$key instanceof \RectorPrefix20211020\Nette\Neon\Node\StringNode && !$key instanceof \RectorPrefix20211020\Nette\Neon\Node\LiteralNode || !\is_scalar($key->value)) {
            $this->tokens->error('Unacceptable key', $key->startPos);
        }
        $k = (string) $key->value;
        if (\array_key_exists($k, $arr)) {
            $this->tokens->error("Duplicated key '{$k}'", $key->startPos);
        }
        $arr[$k] = \true;
    }
    /** @return mixed */
    public function literalToValue(string $value, bool $isKey = \false)
    {
        if (!$isKey && \array_key_exists($value, self::SIMPLE_TYPES)) {
            if (isset(self::DEPRECATED_TYPES[$value])) {
                \trigger_error("Neon: keyword '{$value}' is deprecated, use true/yes or false/no.", \E_USER_DEPRECATED);
            }
            return self::SIMPLE_TYPES[$value];
        } elseif (\is_numeric($value)) {
            return $value * 1;
        } elseif (\preg_match(self::PATTERN_HEX, $value)) {
            return \hexdec($value);
        } elseif (\preg_match(self::PATTERN_OCTAL, $value)) {
            return \octdec($value);
        } elseif (\preg_match(self::PATTERN_BINARY, $value)) {
            return \bindec($value);
        } elseif (!$isKey && \preg_match(self::PATTERN_DATETIME, $value)) {
            return new \DateTimeImmutable($value);
        } else {
            return $value;
        }
    }
}
