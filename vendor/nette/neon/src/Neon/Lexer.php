<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Nette\Neon;

/** @internal */
final class Lexer
{
    public const Patterns = [
        // strings
        Token::String => <<<'XX'
'''\n (?:(?: [^\n] | \n(?![\t ]*+''') )*+ \n)?[\t ]*+''' |
"""\n (?:(?: [^\n] | \n(?![\t ]*+""") )*+ \n)?[\t ]*+""" |
' (?: '' | [^'\n] )*+ ' |
" (?: \\. | [^"\\\n] )*+ "
XX
,
        // literal / boolean / integer / float
        Token::Literal => <<<'XX'
(?: [^#"',:=[\]{}()\n\t `-] | (?<!["']) [:-] [^"',=[\]{}()\n\t ] )
(?:
	[^,:=\]})(\n\t ]++ |
	:(?! [\n\t ,\]})] | $ ) |
	[ \t]++ [^#,:=\]})(\n\t ]
)*+
XX
,
        // punctuation
        Token::Char => '[,:=[\\]{}()-]',
        // comment
        Token::Comment => '\\#.*+',
        // new line
        Token::Newline => '\\n++',
        // whitespace
        Token::Whitespace => '[\\t ]++',
    ];
    public function tokenize(string $input) : TokenStream
    {
        $input = \str_replace("\r", '', $input);
        $pattern = '~(' . \implode(')|(', self::Patterns) . ')~Amixu';
        $res = \preg_match_all($pattern, $input, $matches, \PREG_SET_ORDER);
        if ($res === \false) {
            throw new Exception('Invalid UTF-8 sequence.');
        }
        $types = \array_keys(self::Patterns);
        $offset = 0;
        $tokens = [];
        foreach ($matches as $match) {
            $type = $types[\count($match) - 2];
            $tokens[] = new Token($match[0], $type === Token::Char ? $match[0] : $type);
            $offset += \strlen($match[0]);
        }
        $stream = new TokenStream($tokens);
        if ($offset !== \strlen($input)) {
            $s = \str_replace("\n", '\\n', \substr($input, $offset, 40));
            $stream->error("Unexpected '{$s}'", \count($tokens));
        }
        return $stream;
    }
    public static function requiresDelimiters(string $s) : bool
    {
        return \preg_match('~[\\x00-\\x1F]|^[+-.]?\\d|^(true|false|yes|no|on|off|null)$~Di', $s) || !\preg_match('~^' . self::Patterns[Token::Literal] . '$~Dx', $s);
    }
}
