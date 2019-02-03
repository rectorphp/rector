<?php declare(strict_types=1);

namespace Rector\Php;

use Nette\Utils\Strings;
use Rector\Php\Exception\InvalidEregException;

/**
 * @author Kang Seonghoon <public+ere2pcre@mearie.org>
 * @source https://gist.github.com/lifthrasiir/704754/7e486f43e62fd1c9d3669330c251f8ca4a59a3f8
 */
final class EregToPcreTransformer
{
    /**
     * @var string
     */
    private $pcreDelimiter;

    /**
     * Change this via services configuratoin in rector.yaml if you need it
     * Single type is chosen to prevent every regular with different delimiter.
     */
    public function __construct(string $pcreDelimiter = '#')
    {
        $this->pcreDelimiter = $pcreDelimiter;
    }

    public function transform(string $ereg, bool $isCaseInsensitive): string
    {
        if (! Strings::contains($ereg, $this->pcreDelimiter)) {
            return $this->ere2pcre($ereg, $isCaseInsensitive);
        }

        // fallback
        return $this->ere2pcre(preg_quote($ereg, '#'), $isCaseInsensitive);
    }

    // converts the ERE $s into the PCRE $r. triggers error on any invalid input.
    public function ere2pcre(string $s, bool $ignorecase): string
    {
        static $cache = [], $icache = [];
        if ($ignorecase) {
            if (isset($icache[$s])) {
                return $icache[$s];
            }
        } else {
            if (isset($cache[$s])) {
                return $cache[$s];
            }
        }
        [$r, $i] = $this->_ere2pcre($s, 0);
        if ($i !== strlen($s)) {
            throw new InvalidEregException('unescaped metacharacter ")"');
        }

        if ($ignorecase) {
            return $icache[$s] = '#' . $r . '#mi';
        }

        return $cache[$s] = '#' . $r . '#m';
    }

    /**
     * Recursively converts ERE into PCRE, starting at the position $i.
     *
     * @return mixed[]
     */
    private function _ere2pcre(string $s, int $i): array
    {
        $r = [''];
        $rr = 0;
        $l = strlen($s);
        while ($i < $l) {
            // atom
            $c = $s[$i];
            if ($c === '(') {
                if ($i + 1 < $l && $s[$i + 1] === ')') { // special case
                    $r[$rr] .= '()';
                    ++$i;
                } else {
                    [$t, $ii] = $this->_ere2pcre($s, $i + 1);
                    if ($ii >= $l || $s[$ii] !== ')') {
                        throw new InvalidEregException('"(" does not have a matching ")"');
                    }
                    $r[$rr] .= '(' . $t . ')';
                    $i = $ii;
                }
            } elseif ($c === '[') {
                ++$i;
                $cls = '';
                if ($i < $l && $s[$i] === '^') {
                    $cls .= '^';
                    ++$i;
                }
                if ($i >= $l) {
                    throw new InvalidEregException('"[" does not have a matching "]"');
                }
                $start = true;
                do {
                    if ($s[$i] === '[' &&
                        $i + 1 < $l && strpos('.=:', $s[$i + 1]) !== false) {
                        $ii = strpos($s, ']', $i);
                        if ($ii === false) {
                            throw new InvalidEregException('"[" does not have a matching ' . '"]"');
                        }
                        $ccls = substr($s, $i + 1, $ii - ($i + 1));
                        $cclsmap = [
                            ':alnum:' => '[:alnum:]',
                            ':alpha:' => '[:alpha:]',
                            ':blank:' => '[:blank:]',
                            ':cntrl:' => '[:cntrl:]',
                            ':digit:' => '\d',
                            ':graph:' => '[:graph:]',
                            ':lower:' => '[:lower:]',
                            ':print:' => '[:print:]',
                            ':punct:' => '[:punct:]',
                            ':space:' => '\013\s', // should include VT
                            ':upper:' => '[:upper:]',
                            ':xdigit:' => '[:xdigit:]',
                        ];
                        if (! isset($cclsmap[$ccls])) {
                            throw new InvalidEregException(
                                'an invalid or unsupported ' .
                                'character class [' . $ccls . ']'
                            );
                        }
                        $cls .= $cclsmap[$ccls];
                        $i = $ii + 1;
                    } else {
                        $a = $s[$i++];
                        if ($a === '-' && ! $start && ! ($i < $l && $s[$i] === ']')) {
                            throw new InvalidEregException(
                                '"-" is invalid for the start ' .
                                'character in the brackets'
                            );
                        }
                        if ($i < $l && $s[$i] === '-') {
                            ++$i;
                            $b = $s[$i++];
                            if ($b === ']') {
                                $cls .= $this->_ere2pcre_escape($a) . '\-';
                                break;
                            } elseif (ord($a) > ord($b)) {
                                throw new InvalidEregException(sprintf('an invalid character range %d-%d"', $a, $b));
                            }
                            $cls .= $this->_ere2pcre_escape($a) . '-' . $this->_ere2pcre_escape($b);
                        } else {
                            $cls .= $this->_ere2pcre_escape($a);
                        }
                    }
                    $start = false;
                } while ($i < $l && $s[$i] !== ']');
                if ($i >= $l) {
                    throw new InvalidEregException('"[" does not have a matching "]"');
                }
                $r[$rr] .= '[' . $cls . ']';
            } elseif ($c === ')') {
                break;
            } elseif ($c === '*' || $c === '+' || $c === '?') {
                throw new InvalidEregException('unescaped metacharacter "' . $c . '"');
            } elseif ($c === '{') {
                if ($i + 1 < $l && strpos('0123456789', $s[$i + 1]) !== false) {
                    $r[$rr] .= '\{';
                } else {
                    throw new InvalidEregException('unescaped metacharacter "' . $c . '"');
                }
            } elseif ($c === '.') {
                $r[$rr] .= $c;
            } elseif ($c === '^' || $c === '$') {
                $r[$rr] .= $c;
                ++$i;
                continue;
            } elseif ($c === '|') {
                if ($r[$rr] === '') {
                    throw new InvalidEregException('empty branch');
                }
                $r[] = '';
                ++$rr;
                ++$i;
                continue;
            } elseif ($c === '\\') {
                if (++$i >= $l) {
                    throw new InvalidEregException('an invalid escape sequence at the end');
                }
                $r[$rr] .= $this->_ere2pcre_escape($s[$i]);
            } else { // including ] and } which are allowed as a literal character
                $r[$rr] .= $this->_ere2pcre_escape($c);
            }
            ++$i;
            if ($i >= $l) {
                break;
            }
            // piece after the atom (only ONE of them is possible)
            $c = $s[$i];
            if ($c === '*' || $c === '+' || $c === '?') {
                $r[$rr] .= $c;
                ++$i;
            } elseif ($c === '{') {
                $ii = strpos($s, '}', $i);
                if ($ii === false) {
                    throw new InvalidEregException('"{" does not have a matching "}"');
                }
                $bound = substr($s, $i + 1, $ii - ($i + 1));
                if (! preg_match(
                    '/^([0-9]|[1-9][0-9]|1[0-9][0-9]|
                                2[0-4][0-9]|25[0-5])
                               (,([0-9]|[1-9][0-9]|1[0-9][0-9]|
                                  2[0-4][0-9]|25[0-5])?)?$/x',
                    $bound,
                    $m
                )) {
                    throw new InvalidEregException('an invalid bound');
                }
                if (isset($m[3])) {
                    if ($m[1] > $m[3]) {
                        throw new InvalidEregException('an invalid bound');
                    }
                    $r[$rr] .= '{' . $m[1] . ',' . $m[3] . '}';
                } elseif (isset($m[2])) {
                    $r[$rr] .= '{' . $m[1] . ',}';
                } else {
                    $r[$rr] .= '{' . $m[1] . '}';
                }
                $i = $ii + 1;
            }
        }
        if ($r[$rr] === '') {
            throw new InvalidEregException('empty regular expression or branch');
        }

        return [implode('|', $r), $i];
    }

    private function _ere2pcre_escape(string $c): string
    {
        if ($c === "\0") {
            throw new InvalidEregException('a literal null byte in the regex');
        } elseif (strpos('\^$.[]|()?*+{}-/', $c) !== false) {
            return '\\' . $c;
        }

        return $c;
    }
}
