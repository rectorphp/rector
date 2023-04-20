<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Lexer;

use function implode;
use function preg_match_all;
use const PREG_SET_ORDER;
/**
 * Implementation based on Nette Tokenizer (New BSD License; https://github.com/nette/tokenizer)
 */
class Lexer
{
    public const TOKEN_REFERENCE = 0;
    public const TOKEN_UNION = 1;
    public const TOKEN_INTERSECTION = 2;
    public const TOKEN_NULLABLE = 3;
    public const TOKEN_OPEN_PARENTHESES = 4;
    public const TOKEN_CLOSE_PARENTHESES = 5;
    public const TOKEN_OPEN_ANGLE_BRACKET = 6;
    public const TOKEN_CLOSE_ANGLE_BRACKET = 7;
    public const TOKEN_OPEN_SQUARE_BRACKET = 8;
    public const TOKEN_CLOSE_SQUARE_BRACKET = 9;
    public const TOKEN_COMMA = 10;
    public const TOKEN_VARIADIC = 11;
    public const TOKEN_DOUBLE_COLON = 12;
    public const TOKEN_DOUBLE_ARROW = 13;
    public const TOKEN_EQUAL = 14;
    public const TOKEN_OPEN_PHPDOC = 15;
    public const TOKEN_CLOSE_PHPDOC = 16;
    public const TOKEN_PHPDOC_TAG = 17;
    public const TOKEN_FLOAT = 18;
    public const TOKEN_INTEGER = 19;
    public const TOKEN_SINGLE_QUOTED_STRING = 20;
    public const TOKEN_DOUBLE_QUOTED_STRING = 21;
    public const TOKEN_IDENTIFIER = 22;
    public const TOKEN_THIS_VARIABLE = 23;
    public const TOKEN_VARIABLE = 24;
    public const TOKEN_HORIZONTAL_WS = 25;
    public const TOKEN_PHPDOC_EOL = 26;
    public const TOKEN_OTHER = 27;
    public const TOKEN_END = 28;
    public const TOKEN_COLON = 29;
    public const TOKEN_WILDCARD = 30;
    public const TOKEN_OPEN_CURLY_BRACKET = 31;
    public const TOKEN_CLOSE_CURLY_BRACKET = 32;
    public const TOKEN_NEGATED = 33;
    public const TOKEN_ARROW = 34;
    public const TOKEN_LABELS = [self::TOKEN_REFERENCE => '\'&\'', self::TOKEN_UNION => '\'|\'', self::TOKEN_INTERSECTION => '\'&\'', self::TOKEN_NULLABLE => '\'?\'', self::TOKEN_NEGATED => '\'!\'', self::TOKEN_OPEN_PARENTHESES => '\'(\'', self::TOKEN_CLOSE_PARENTHESES => '\')\'', self::TOKEN_OPEN_ANGLE_BRACKET => '\'<\'', self::TOKEN_CLOSE_ANGLE_BRACKET => '\'>\'', self::TOKEN_OPEN_SQUARE_BRACKET => '\'[\'', self::TOKEN_CLOSE_SQUARE_BRACKET => '\']\'', self::TOKEN_OPEN_CURLY_BRACKET => '\'{\'', self::TOKEN_CLOSE_CURLY_BRACKET => '\'}\'', self::TOKEN_COMMA => '\',\'', self::TOKEN_COLON => '\':\'', self::TOKEN_VARIADIC => '\'...\'', self::TOKEN_DOUBLE_COLON => '\'::\'', self::TOKEN_DOUBLE_ARROW => '\'=>\'', self::TOKEN_ARROW => '\'->\'', self::TOKEN_EQUAL => '\'=\'', self::TOKEN_OPEN_PHPDOC => '\'/**\'', self::TOKEN_CLOSE_PHPDOC => '\'*/\'', self::TOKEN_PHPDOC_TAG => 'TOKEN_PHPDOC_TAG', self::TOKEN_PHPDOC_EOL => 'TOKEN_PHPDOC_EOL', self::TOKEN_FLOAT => 'TOKEN_FLOAT', self::TOKEN_INTEGER => 'TOKEN_INTEGER', self::TOKEN_SINGLE_QUOTED_STRING => 'TOKEN_SINGLE_QUOTED_STRING', self::TOKEN_DOUBLE_QUOTED_STRING => 'TOKEN_DOUBLE_QUOTED_STRING', self::TOKEN_IDENTIFIER => 'type', self::TOKEN_THIS_VARIABLE => '\'$this\'', self::TOKEN_VARIABLE => 'variable', self::TOKEN_HORIZONTAL_WS => 'TOKEN_HORIZONTAL_WS', self::TOKEN_OTHER => 'TOKEN_OTHER', self::TOKEN_END => 'TOKEN_END', self::TOKEN_WILDCARD => '*'];
    public const VALUE_OFFSET = 0;
    public const TYPE_OFFSET = 1;
    public const LINE_OFFSET = 2;
    /** @var string|null */
    private $regexp;
    /**
     * @return list<array{string, int, int}>
     */
    public function tokenize(string $s) : array
    {
        if ($this->regexp === null) {
            $this->regexp = $this->generateRegexp();
        }
        preg_match_all($this->regexp, $s, $matches, PREG_SET_ORDER);
        $tokens = [];
        $line = 1;
        foreach ($matches as $match) {
            $type = (int) $match['MARK'];
            $tokens[] = [$match[0], $type, $line];
            if ($type !== self::TOKEN_PHPDOC_EOL) {
                continue;
            }
            $line++;
        }
        $tokens[] = ['', self::TOKEN_END, $line];
        return $tokens;
    }
    private function generateRegexp() : string
    {
        $patterns = [
            self::TOKEN_HORIZONTAL_WS => '[\\x09\\x20]++',
            self::TOKEN_IDENTIFIER => '(?:[\\\\]?+[a-z_\\x80-\\xFF][0-9a-z_\\x80-\\xFF-]*+)++',
            self::TOKEN_THIS_VARIABLE => '\\$this(?![0-9a-z_\\x80-\\xFF])',
            self::TOKEN_VARIABLE => '\\$[a-z_\\x80-\\xFF][0-9a-z_\\x80-\\xFF]*+',
            // '&' followed by TOKEN_VARIADIC, TOKEN_VARIABLE, TOKEN_EQUAL, TOKEN_EQUAL or TOKEN_CLOSE_PARENTHESES
            self::TOKEN_REFERENCE => '&(?=\\s*+(?:[.,=)]|(?:\\$(?!this(?![0-9a-z_\\x80-\\xFF])))))',
            self::TOKEN_UNION => '\\|',
            self::TOKEN_INTERSECTION => '&',
            self::TOKEN_NULLABLE => '\\?',
            self::TOKEN_NEGATED => '!',
            self::TOKEN_OPEN_PARENTHESES => '\\(',
            self::TOKEN_CLOSE_PARENTHESES => '\\)',
            self::TOKEN_OPEN_ANGLE_BRACKET => '<',
            self::TOKEN_CLOSE_ANGLE_BRACKET => '>',
            self::TOKEN_OPEN_SQUARE_BRACKET => '\\[',
            self::TOKEN_CLOSE_SQUARE_BRACKET => '\\]',
            self::TOKEN_OPEN_CURLY_BRACKET => '\\{',
            self::TOKEN_CLOSE_CURLY_BRACKET => '\\}',
            self::TOKEN_COMMA => ',',
            self::TOKEN_VARIADIC => '\\.\\.\\.',
            self::TOKEN_DOUBLE_COLON => '::',
            self::TOKEN_DOUBLE_ARROW => '=>',
            self::TOKEN_ARROW => '->',
            self::TOKEN_EQUAL => '=',
            self::TOKEN_COLON => ':',
            self::TOKEN_OPEN_PHPDOC => '/\\*\\*(?=\\s)\\x20?+',
            self::TOKEN_CLOSE_PHPDOC => '\\*/',
            self::TOKEN_PHPDOC_TAG => '@(?:[a-z][a-z0-9-\\\\]+:)?[a-z][a-z0-9-\\\\]*+',
            self::TOKEN_PHPDOC_EOL => '\\r?+\\n[\\x09\\x20]*+(?:\\*(?!/)\\x20?+)?',
            self::TOKEN_FLOAT => '(?:-?[0-9]++\\.[0-9]*+(?:e-?[0-9]++)?)|(?:-?[0-9]*+\\.[0-9]++(?:e-?[0-9]++)?)|(?:-?[0-9]++e-?[0-9]++)',
            self::TOKEN_INTEGER => '-?(?:(?:0b[0-1]++)|(?:0o[0-7]++)|(?:0x[0-9a-f]++)|(?:[0-9]++))',
            self::TOKEN_SINGLE_QUOTED_STRING => '\'(?:\\\\[^\\r\\n]|[^\'\\r\\n\\\\])*+\'',
            self::TOKEN_DOUBLE_QUOTED_STRING => '"(?:\\\\[^\\r\\n]|[^"\\r\\n\\\\])*+"',
            self::TOKEN_WILDCARD => '\\*',
            // anything but TOKEN_CLOSE_PHPDOC or TOKEN_HORIZONTAL_WS or TOKEN_EOL
            self::TOKEN_OTHER => '(?:(?!\\*/)[^\\s])++',
        ];
        foreach ($patterns as $type => &$pattern) {
            $pattern = '(?:' . $pattern . ')(*MARK:' . $type . ')';
        }
        return '~' . implode('|', $patterns) . '~Asi';
    }
}
