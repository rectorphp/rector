<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210827\Nette\Neon;

/**
 * Parser for Nette Object Notation.
 * @internal
 */
final class Decoder
{
    public const PATTERNS = [
        // strings
        '
			\'\'\'\\n (?:(?: [^\\n] | \\n(?![\\t\\ ]*+\'\'\') )*+ \\n)?[\\t\\ ]*+\'\'\' |
			"""\\n (?:(?: [^\\n] | \\n(?![\\t\\ ]*+""") )*+ \\n)?[\\t\\ ]*+""" |
			\' (?: \'\' | [^\'\\n] )*+ \' |
			" (?: \\\\. | [^"\\\\\\n] )*+ "
		',
        // literal / boolean / integer / float
        '
			(?: [^#"\',:=[\\]{}()\\n\\t\\ `-] | (?<!["\']) [:-] [^"\',=[\\]{}()\\n\\t\\ ] )
			(?:
				[^,:=\\]})(\\n\\t\\ ]++ |
				:(?! [\\n\\t\\ ,\\]})] | $ ) |
				[\\ \\t]++ [^#,:=\\]})(\\n\\t\\ ]
			)*+
		',
        // punctuation
        '[,:=[\\]{}()-]',
        // comment
        '?:\\#.*+',
        // new line + indent
        '\\n[\\t\\ ]*+',
        // whitespace
        '?:[\\t\\ ]++',
    ];
    private const PATTERN_DATETIME = '#\\d\\d\\d\\d-\\d\\d?-\\d\\d?(?:(?:[Tt]| ++)\\d\\d?:\\d\\d:\\d\\d(?:\\.\\d*+)? *+(?:Z|[-+]\\d\\d?(?::?\\d\\d)?)?)?$#DA';
    private const PATTERN_HEX = '#0x[0-9a-fA-F]++$#DA';
    private const PATTERN_OCTAL = '#0o[0-7]++$#DA';
    private const PATTERN_BINARY = '#0b[0-1]++$#DA';
    private const SIMPLE_TYPES = ['true' => 'TRUE', 'True' => 'TRUE', 'TRUE' => 'TRUE', 'yes' => 'TRUE', 'Yes' => 'TRUE', 'YES' => 'TRUE', 'on' => 'TRUE', 'On' => 'TRUE', 'ON' => 'TRUE', 'false' => 'FALSE', 'False' => 'FALSE', 'FALSE' => 'FALSE', 'no' => 'FALSE', 'No' => 'FALSE', 'NO' => 'FALSE', 'off' => 'FALSE', 'Off' => 'FALSE', 'OFF' => 'FALSE', 'null' => 'NULL', 'Null' => 'NULL', 'NULL' => 'NULL'];
    private const DEPRECATED_TYPES = ['on' => 1, 'On' => 1, 'ON' => 1, 'off' => 1, 'Off' => 1, 'OFF' => 1];
    private const ESCAPE_SEQUENCES = ['t' => "\t", 'n' => "\n", 'r' => "\r", 'f' => "\f", 'b' => "\10", '"' => '"', '\\' => '\\', '/' => '/', '_' => " "];
    private const BRACKETS = ['[' => ']', '{' => '}', '(' => ')'];
    /** @var string */
    private $input;
    /** @var array */
    private $tokens;
    /** @var int */
    private $pos;
    /**
     * Decodes a NEON string.
     * @return mixed
     */
    public function decode(string $input)
    {
        if (\substr($input, 0, 3) === "﻿") {
            // BOM
            $input = \substr($input, 3);
        }
        $this->input = "\n" . \str_replace("\r", '', $input);
        // \n forces indent detection
        $pattern = '~(' . \implode(')|(', self::PATTERNS) . ')~Amixu';
        $this->tokens = \preg_split($pattern, $this->input, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_OFFSET_CAPTURE | \PREG_SPLIT_DELIM_CAPTURE);
        if ($this->tokens === \false) {
            throw new \RectorPrefix20210827\Nette\Neon\Exception('Invalid UTF-8 sequence.');
        }
        $last = \end($this->tokens);
        if ($this->tokens && !\preg_match($pattern, $last[0])) {
            $this->pos = \count($this->tokens) - 1;
            $this->error();
        }
        $this->pos = 0;
        $res = $this->parse(null);
        while (isset($this->tokens[$this->pos])) {
            if ($this->tokens[$this->pos][0][0] === "\n") {
                $this->pos++;
            } else {
                $this->error();
            }
        }
        return $res;
    }
    /**
     * @param  string|bool|null  $indent  indentation (for block-parser)
     * @return mixed
     */
    private function parse($indent, array $result = null, $key = null, bool $hasKey = \false)
    {
        $inlineParser = $indent === \false;
        $value = null;
        $hasValue = \false;
        $tokens = $this->tokens;
        $n =& $this->pos;
        $count = \count($tokens);
        $mainResult =& $result;
        for (; $n < $count; $n++) {
            $t = $tokens[$n][0];
            if ($t === ',') {
                // ArrayEntry separator
                if (!$hasKey && !$hasValue || !$inlineParser) {
                    $this->error();
                }
                $this->addValue($result, $hasKey ? $key : null, $hasValue ? $value : null);
                $hasKey = $hasValue = \false;
            } elseif ($t === ':' || $t === '=') {
                // KeyValuePair separator
                if ($hasValue && (\is_array($value) || \is_object($value))) {
                    $this->error('Unacceptable key');
                } elseif ($hasKey && $key === null && $hasValue && !$inlineParser) {
                    $n++;
                    $result[] = $this->parse($indent . '  ', [], $value, \true);
                    $newIndent = isset($tokens[$n], $tokens[$n + 1]) ? (string) \substr($tokens[$n][0], 1) : '';
                    if (\strlen($newIndent) > \strlen($indent)) {
                        $n++;
                        $this->error('Bad indentation');
                    } elseif (\strlen($newIndent) < \strlen($indent)) {
                        return $mainResult;
                        // block parser exit point
                    }
                    $hasKey = $hasValue = \false;
                } elseif ($hasKey || !$hasValue) {
                    $this->error();
                } else {
                    $key = (string) $value;
                    $hasKey = \true;
                    $hasValue = \false;
                    $result =& $mainResult;
                }
            } elseif ($t === '-') {
                // BlockArray bullet
                if ($hasKey || $hasValue || $inlineParser) {
                    $this->error();
                }
                $key = null;
                $hasKey = \true;
            } elseif (isset(self::BRACKETS[$t])) {
                // Opening bracket [ ( {
                if ($hasValue) {
                    if ($t !== '(') {
                        $this->error();
                    }
                    $n++;
                    if ($value instanceof \RectorPrefix20210827\Nette\Neon\Entity && $value->value === \RectorPrefix20210827\Nette\Neon\Neon::CHAIN) {
                        \end($value->attributes)->attributes = $this->parse(\false, []);
                    } else {
                        $value = new \RectorPrefix20210827\Nette\Neon\Entity($value, $this->parse(\false, []));
                    }
                } else {
                    $n++;
                    $value = $this->parse(\false, []);
                }
                $hasValue = \true;
                if (!isset($tokens[$n]) || $tokens[$n][0] !== self::BRACKETS[$t]) {
                    $this->error();
                }
            } elseif ($t === ']' || $t === '}' || $t === ')') {
                // Closing bracket ] ) }
                if (!$inlineParser) {
                    $this->error();
                }
                break;
            } elseif ($t[0] === "\n") {
                // Indent
                if ($inlineParser) {
                    if ($hasKey || $hasValue) {
                        $this->addValue($result, $hasKey ? $key : null, $hasValue ? $value : null);
                        $hasKey = $hasValue = \false;
                    }
                } else {
                    while (isset($tokens[$n + 1]) && $tokens[$n + 1][0][0] === "\n") {
                        $n++;
                        // skip to last indent
                    }
                    if (!isset($tokens[$n + 1])) {
                        break;
                    }
                    $newIndent = (string) \substr($tokens[$n][0], 1);
                    if ($indent === null) {
                        // first iteration
                        $indent = $newIndent;
                    }
                    $minlen = \min(\strlen($newIndent), \strlen($indent));
                    if ($minlen && (string) \substr($newIndent, 0, $minlen) !== (string) \substr($indent, 0, $minlen)) {
                        $n++;
                        $this->error('Invalid combination of tabs and spaces');
                    }
                    if (\strlen($newIndent) > \strlen($indent)) {
                        // open new block-array or hash
                        if ($hasValue || !$hasKey) {
                            $n++;
                            $this->error('Bad indentation');
                        }
                        $this->addValue($result, $key, $this->parse($newIndent));
                        $newIndent = isset($tokens[$n], $tokens[$n + 1]) ? (string) \substr($tokens[$n][0], 1) : '';
                        if (\strlen($newIndent) > \strlen($indent)) {
                            $n++;
                            $this->error('Bad indentation');
                        }
                        $hasKey = \false;
                    } else {
                        if ($hasValue && !$hasKey) {
                            // block items must have "key"; null key means list item
                            break;
                        } elseif ($hasKey) {
                            $this->addValue($result, $key, $hasValue ? $value : null);
                            if ($key !== null && !$hasValue && $newIndent === $indent && isset($tokens[$n + 1]) && $tokens[$n + 1][0] === '-') {
                                $result =& $result[$key];
                            }
                            $hasKey = $hasValue = \false;
                        }
                    }
                    if (\strlen($newIndent) < \strlen($indent)) {
                        // close block
                        return $mainResult;
                        // block parser exit point
                    }
                }
            } else {
                // Value
                $isKey = ($tmp = $tokens[$n + 1][0] ?? null) && ($tmp === ':' || $tmp === '=');
                if ($t[0] === '"' || $t[0] === "'") {
                    if (\preg_match('#^...\\n++([\\t ]*+)#', $t, $m)) {
                        $converted = \substr($t, 3, -3);
                        $converted = \str_replace("\n" . $m[1], "\n", $converted);
                        $converted = \preg_replace('#^\\n|\\n[\\t ]*+$#D', '', $converted);
                    } else {
                        $converted = \substr($t, 1, -1);
                        if ($t[0] === "'") {
                            $converted = \str_replace("''", "'", $converted);
                        }
                    }
                    if ($t[0] === '"') {
                        $converted = \preg_replace_callback('#\\\\(?:ud[89ab][0-9a-f]{2}\\\\ud[c-f][0-9a-f]{2}|u[0-9a-f]{4}|x[0-9a-f]{2}|.)#i', [$this, 'cbString'], $converted);
                    }
                } elseif (!$isKey && isset(self::SIMPLE_TYPES[$t])) {
                    $converted = \constant(self::SIMPLE_TYPES[$t]);
                    if (isset(self::DEPRECATED_TYPES[$t])) {
                        \trigger_error("Neon: keyword '{$t}' is deprecated, use true/yes or false/no.", \E_USER_DEPRECATED);
                    }
                } elseif (\is_numeric($t)) {
                    $converted = $t * 1;
                } elseif (\preg_match(self::PATTERN_HEX, $t)) {
                    $converted = \hexdec($t);
                } elseif (\preg_match(self::PATTERN_OCTAL, $t)) {
                    $converted = \octdec($t);
                } elseif (\preg_match(self::PATTERN_BINARY, $t)) {
                    $converted = \bindec($t);
                } elseif (!$isKey && \preg_match(self::PATTERN_DATETIME, $t)) {
                    $converted = new \DateTimeImmutable($t);
                } else {
                    // literal
                    $converted = $t;
                }
                if ($hasValue) {
                    if ($value instanceof \RectorPrefix20210827\Nette\Neon\Entity) {
                        // Entity chaining
                        if ($value->value !== \RectorPrefix20210827\Nette\Neon\Neon::CHAIN) {
                            $value = new \RectorPrefix20210827\Nette\Neon\Entity(\RectorPrefix20210827\Nette\Neon\Neon::CHAIN, [$value]);
                        }
                        $value->attributes[] = new \RectorPrefix20210827\Nette\Neon\Entity($converted);
                    } else {
                        $this->error();
                    }
                } else {
                    $value = $converted;
                    $hasValue = \true;
                }
            }
        }
        if ($inlineParser) {
            if ($hasKey || $hasValue) {
                $this->addValue($result, $hasKey ? $key : null, $hasValue ? $value : null);
            }
        } else {
            if ($hasValue && !$hasKey) {
                // block items must have "key"
                if ($result === null) {
                    $result = $value;
                    // simple value parser
                } else {
                    $this->error();
                }
            } elseif ($hasKey) {
                $this->addValue($result, $key, $hasValue ? $value : null);
            }
        }
        return $mainResult;
    }
    private function addValue(&$result, $key, $value)
    {
        if ($key === null) {
            $result[] = $value;
        } elseif ($result && \array_key_exists($key, $result)) {
            $this->error("Duplicated key '{$key}'");
        } else {
            $result[$key] = $value;
        }
    }
    private function cbString(array $m) : string
    {
        $sq = $m[0];
        if (isset(self::ESCAPE_SEQUENCES[$sq[1]])) {
            return self::ESCAPE_SEQUENCES[$sq[1]];
        } elseif ($sq[1] === 'u' && \strlen($sq) >= 6) {
            $lead = \hexdec(\substr($sq, 2, 4));
            $tail = \hexdec(\substr($sq, 8, 4));
            $code = $tail ? 0x2400 + ($lead - 0xd800 << 10) + $tail : $lead;
            if ($code >= 0xd800 && $code <= 0xdfff) {
                $this->error("Invalid UTF-8 (lone surrogate) {$sq}");
            }
            return \function_exists('iconv') ? \iconv('UTF-32BE', 'UTF-8//IGNORE', \pack('N', $code)) : \mb_convert_encoding(\pack('N', $code), 'UTF-8', 'UTF-32BE');
        } elseif ($sq[1] === 'x' && \strlen($sq) === 4) {
            \trigger_error("Neon: '{$sq}' is deprecated, use '\\uXXXX' instead.", \E_USER_DEPRECATED);
            return \chr(\hexdec(\substr($sq, 2)));
        } else {
            $this->error("Invalid escaping sequence {$sq}");
        }
    }
    private function error(string $message = "Unexpected '%s'")
    {
        $last = $this->tokens[$this->pos] ?? null;
        $offset = $last ? $last[1] : \strlen($this->input);
        $text = \substr($this->input, 0, $offset);
        $line = \substr_count($text, "\n");
        $col = $offset - \strrpos("\n" . $text, "\n") + 1;
        $token = $last ? \str_replace("\n", '<new line>', \substr($last[0], 0, 40)) : 'end';
        throw new \RectorPrefix20210827\Nette\Neon\Exception(\str_replace('%s', $token, $message) . " on line {$line}, column {$col}.");
    }
}
