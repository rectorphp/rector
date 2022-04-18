<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220418\Nette\Neon;

/** @internal */
final class TokenStream
{
    /** @var Token[] */
    private $tokens;
    /** @var int */
    private $pos = 0;
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }
    public function getPos() : int
    {
        return $this->pos;
    }
    /** @return Token[] */
    public function getTokens() : array
    {
        return $this->tokens;
    }
    public function isNext(...$types) : bool
    {
        while (\in_array($this->tokens[$this->pos]->type ?? null, [\RectorPrefix20220418\Nette\Neon\Token::COMMENT, \RectorPrefix20220418\Nette\Neon\Token::WHITESPACE], \true)) {
            $this->pos++;
        }
        return $types ? \in_array($this->tokens[$this->pos]->type ?? null, $types, \true) : isset($this->tokens[$this->pos]);
    }
    public function consume(...$types) : ?\RectorPrefix20220418\Nette\Neon\Token
    {
        return $this->isNext(...$types) ? $this->tokens[$this->pos++] : null;
    }
    public function getIndentation() : string
    {
        return \in_array($this->tokens[$this->pos - 2]->type ?? null, [\RectorPrefix20220418\Nette\Neon\Token::NEWLINE, null], \true) && ($this->tokens[$this->pos - 1]->type ?? null) === \RectorPrefix20220418\Nette\Neon\Token::WHITESPACE ? $this->tokens[$this->pos - 1]->value : '';
    }
    /** @return never */
    public function error(string $message = null, int $pos = null) : void
    {
        $pos = $pos ?? $this->pos;
        $input = '';
        foreach ($this->tokens as $i => $token) {
            if ($i >= $pos) {
                break;
            }
            $input .= $token->value;
        }
        $line = \substr_count($input, "\n") + 1;
        $col = \strlen($input) - \strrpos("\n" . $input, "\n") + 1;
        $token = $this->tokens[$pos] ?? null;
        $message = $message ?? 'Unexpected ' . ($token === null ? 'end' : "'" . \str_replace("\n", '<new line>', \substr($this->tokens[$pos]->value, 0, 40)) . "'");
        throw new \RectorPrefix20220418\Nette\Neon\Exception("{$message} on line {$line}, column {$col}.");
    }
}
