<?php

declare (strict_types=1);
namespace Rector\Php70;

use RectorPrefix202407\Nette\Utils\Strings;
use Rector\Php70\Exception\InvalidEregException;
/**
 * @changelog https://gist.github.com/lifthrasiir/704754/7e486f43e62fd1c9d3669330c251f8ca4a59a3f8
 *
 * @see \Rector\Tests\Php70\EregToPcreTransformerTest
 */
final class EregToPcreTransformer
{
    /**
     * @readonly
     * @var string
     */
    private $pcreDelimiter = '#';
    /**
     * @var array<string, string>
     */
    private const CHARACTER_CLASS_MAP = [
        ':alnum:' => '[:alnum:]',
        ':alpha:' => '[:alpha:]',
        ':blank:' => '[:blank:]',
        ':cntrl:' => '[:cntrl:]',
        ':digit:' => '\\d',
        ':graph:' => '[:graph:]',
        ':lower:' => '[:lower:]',
        ':print:' => '[:print:]',
        ':punct:' => '[:punct:]',
        // should include VT
        ':space:' => '013\\s',
        ':upper:' => '[:upper:]',
        ':xdigit:' => '[:xdigit:]',
    ];
    /**
     * @var string
     * @see https://regex101.com/r/htpXFg/1
     */
    private const BOUND_REGEX = '/^(?<' . self::MINIMAL_NUMBER_PART . '>\\d|[1-9]\\d|1\\d\\d|
                                2[0-4]\\d|25[0-5])
                               (?<comma>,(?<' . self::MAXIMAL_NUMBER_PART . '>\\d|[1-9]\\d|1\\d\\d|
                                  2[0-4]\\d|25[0-5])?)?$/x';
    /**
     * @var string
     */
    private const MINIMAL_NUMBER_PART = 'minimal_number';
    /**
     * @var string
     */
    private const MAXIMAL_NUMBER_PART = 'maximal_number';
    /**
     * @var array<string, string>
     */
    private $icache = [];
    /**
     * @var array<string, string>
     */
    private $cache = [];
    /**
     * Change this via services configuratoin in rector.php if you need it
     * Single type is chosen to prevent every regular with different delimiter.
     */
    public function __construct(string $pcreDelimiter = '#')
    {
        $this->pcreDelimiter = $pcreDelimiter;
    }
    public function transform(string $ereg, bool $isCaseInsensitive) : string
    {
        if (\strpos($ereg, $this->pcreDelimiter) === \false) {
            return $this->ere2pcre($ereg, $isCaseInsensitive);
        }
        // fallback
        $quotedEreg = \preg_quote($ereg, '#');
        return $this->ere2pcre($quotedEreg, $isCaseInsensitive);
    }
    // converts the ERE $s into the PCRE $r. triggers error on any invalid input.
    private function ere2pcre(string $content, bool $ignorecase) : string
    {
        if ($ignorecase) {
            if (isset($this->icache[$content])) {
                return $this->icache[$content];
            }
        } elseif (isset($this->cache[$content])) {
            return $this->cache[$content];
        }
        [$r, $i] = $this->_ere2pcre($content, 0);
        if ($i !== \strlen($content)) {
            throw new InvalidEregException('unescaped metacharacter ")"');
        }
        if ($ignorecase) {
            return $this->icache[$content] = '#' . $r . '#mi';
        }
        return $this->cache[$content] = '#' . $r . '#m';
    }
    /**
     * Recursively converts ERE into PCRE, starting at the position $i.
     *
     * @return float[]|int[]|string[]
     */
    private function _ere2pcre(string $content, int $i) : array
    {
        $r = [''];
        $rr = 0;
        $l = \strlen($content);
        while ($i < $l) {
            // atom
            $char = $content[$i];
            if ($char === '(') {
                $i = (int) $i;
                $i = $this->processBracket($content, $i, $l, $r, $rr);
            } elseif ($char === '[') {
                ++$i;
                $cls = '';
                if ($i < $l && $content[$i] === '^') {
                    $cls .= '^';
                    ++$i;
                }
                if ($i >= $l) {
                    throw new InvalidEregException('"[" does not have a matching "]"');
                }
                $start = \true;
                $i = (int) $i;
                [$cls, $i] = $this->processSquareBracket($content, $i, $l, $cls, $start);
                if ($i >= $l) {
                    throw new InvalidEregException('"[" does not have a matching "]"');
                }
                $r[$rr] .= '[' . $cls . ']';
            } elseif ($char === ')') {
                break;
            } elseif ($char === '*' || $char === '+' || $char === '?') {
                throw new InvalidEregException('unescaped metacharacter "' . $char . '"');
            } elseif ($char === '{') {
                if ($i + 1 < $l && \strpos('0123456789', $content[$i + 1]) !== \false) {
                    $r[$rr] .= '\\{';
                } else {
                    throw new InvalidEregException('unescaped metacharacter "' . $char . '"');
                }
            } elseif ($char === '.') {
                $r[$rr] .= $char;
            } elseif ($char === '^' || $char === '$') {
                $r[$rr] .= $char;
                ++$i;
                continue;
            } elseif ($char === '|') {
                if ($r[$rr] === '') {
                    throw new InvalidEregException('empty branch');
                }
                $r[] = '';
                ++$rr;
                ++$i;
                continue;
            } elseif ($char === '\\') {
                if (++$i >= $l) {
                    throw new InvalidEregException('an invalid escape sequence at the end');
                }
                $r[$rr] .= $this->_ere2pcre_escape($content[$i]);
            } else {
                // including ] and } which are allowed as a literal character
                $r[$rr] .= $this->_ere2pcre_escape($char);
            }
            ++$i;
            if ($i >= $l) {
                break;
            }
            // piece after the atom (only ONE of them is possible)
            $char = $content[$i];
            if ($char === '*' || $char === '+' || $char === '?') {
                $r[$rr] .= $char;
                ++$i;
            } elseif ($char === '{') {
                $i = (int) $i;
                $i = $this->processCurlyBracket($content, $i, $r, $rr);
            }
        }
        if ($r[$rr] === '') {
            throw new InvalidEregException('empty regular expression or branch');
        }
        return [\implode('|', $r), $i];
    }
    /**
     * @param mixed[] $r
     */
    private function processBracket(string $content, int $i, int $l, array &$r, int $rr) : int
    {
        // special case
        if ($i + 1 < $l && $content[$i + 1] === ')') {
            $r[$rr] .= '()';
            ++$i;
        } else {
            $position = $i + 1;
            [$t, $ii] = $this->_ere2pcre($content, $position);
            if ($ii >= $l || $content[$ii] !== ')') {
                throw new InvalidEregException('"(" does not have a matching ")"');
            }
            $r[$rr] .= '(' . $t . ')';
            $i = $ii;
        }
        // retype
        $i = (int) $i;
        return $i;
    }
    /**
     * @return float[]|int[]|string[]
     */
    private function processSquareBracket(string $s, int $i, int $l, string $cls, bool $start) : array
    {
        do {
            if ($s[$i] === '[' && $i + 1 < $l && \strpos('.=:', $s[$i + 1]) !== \false) {
                /** @var string $cls */
                [$cls, $i] = $this->processCharacterClass($s, $i, $cls);
            } else {
                $a = $s[$i];
                ++$i;
                if ($a === '-' && !$start && !($i < $l && $s[$i] === ']')) {
                    throw new InvalidEregException('"-" is invalid for the start character in the brackets');
                }
                if ($i < $l && $s[$i] === '-') {
                    $b = $s[++$i];
                    if ($b === ']') {
                        $cls .= $this->_ere2pcre_escape($a) . '\\-';
                        break;
                    } elseif (\ord($a) > \ord($b)) {
                        $errorMessage = \sprintf('an invalid character range %d-%d"', (int) $a, (int) $b);
                        throw new InvalidEregException($errorMessage);
                    }
                    $cls .= $this->_ere2pcre_escape($a) . '-' . $this->_ere2pcre_escape($b);
                    ++$i;
                } else {
                    $cls .= $this->_ere2pcre_escape($a);
                }
            }
            $start = \false;
        } while ($i < $l && $s[$i] !== ']');
        return [$cls, $i];
    }
    private function _ere2pcre_escape(string $content) : string
    {
        if ($content === "\x00") {
            throw new InvalidEregException('a literal null byte in the regex');
        }
        if (\strpos('\\^$.[]|()?*+{}-/', $content) !== \false) {
            return '\\' . $content;
        }
        return $content;
    }
    /**
     * @param mixed[] $r
     */
    private function processCurlyBracket(string $s, int $i, array &$r, int $rr) : int
    {
        $ii = \strpos($s, '}', $i);
        if ($ii === \false) {
            throw new InvalidEregException('"{" does not have a matching "}"');
        }
        $start = $i + 1;
        $length = $ii - ($i + 1);
        $bound = Strings::substring($s, $start, $length);
        $matches = Strings::match($bound, self::BOUND_REGEX);
        if ($matches === null) {
            throw new InvalidEregException('an invalid bound');
        }
        if (isset($matches[self::MAXIMAL_NUMBER_PART])) {
            if ($matches[self::MINIMAL_NUMBER_PART] > $matches[self::MAXIMAL_NUMBER_PART]) {
                throw new InvalidEregException('an invalid bound');
            }
            $r[$rr] .= '{' . $matches[self::MINIMAL_NUMBER_PART] . ',' . $matches[self::MAXIMAL_NUMBER_PART] . '}';
        } elseif (isset($matches['comma'])) {
            $r[$rr] .= '{' . $matches[self::MINIMAL_NUMBER_PART] . ',}';
        } else {
            $r[$rr] .= '{' . $matches[self::MINIMAL_NUMBER_PART] . '}';
        }
        return $ii + 1;
    }
    /**
     * @return int[]|string[]
     */
    private function processCharacterClass(string $content, int $i, string $cls) : array
    {
        $offset = $i;
        $ii = \strpos($content, ']', $offset);
        if ($ii === \false) {
            throw new InvalidEregException('"[" does not have a matching "]"');
        }
        $start = $i + 1;
        $length = $ii - ($i + 1);
        $ccls = Strings::substring($content, $start, $length);
        if (!isset(self::CHARACTER_CLASS_MAP[$ccls])) {
            throw new InvalidEregException('an invalid or unsupported character class [' . $ccls . ']');
        }
        $cls .= self::CHARACTER_CLASS_MAP[$ccls];
        $i = $ii + 1;
        return [$cls, $i];
    }
}
