<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\String;

use RectorPrefix20211020\Symfony\Component\String\Exception\ExceptionInterface;
use RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException;
use RectorPrefix20211020\Symfony\Component\String\Exception\RuntimeException;
/**
 * Represents a binary-safe string of bytes.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 * @author Hugo Hamon <hugohamon@neuf.fr>
 *
 * @throws ExceptionInterface
 */
class ByteString extends \RectorPrefix20211020\Symfony\Component\String\AbstractString
{
    private const ALPHABET_ALPHANUMERIC = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    public function __construct(string $string = '')
    {
        $this->string = $string;
    }
    /*
     * The following method was derived from code of the Hack Standard Library (v4.40 - 2020-05-03)
     *
     * https://github.com/hhvm/hsl/blob/80a42c02f036f72a42f0415e80d6b847f4bf62d5/src/random/private.php#L16
     *
     * Code subject to the MIT license (https://github.com/hhvm/hsl/blob/master/LICENSE).
     *
     * Copyright (c) 2004-2020, Facebook, Inc. (https://www.facebook.com/)
     */
    /**
     * @param int $length
     * @param string|null $alphabet
     */
    public static function fromRandom($length = 16, $alphabet = null) : self
    {
        if ($length <= 0) {
            throw new \RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException(\sprintf('A strictly positive length is expected, "%d" given.', $length));
        }
        $alphabet = $alphabet ?? self::ALPHABET_ALPHANUMERIC;
        $alphabetSize = \strlen($alphabet);
        $bits = (int) \ceil(\log($alphabetSize, 2.0));
        if ($bits <= 0 || $bits > 56) {
            throw new \RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException('The length of the alphabet must in the [2^1, 2^56] range.');
        }
        $ret = '';
        while ($length > 0) {
            $urandomLength = (int) \ceil(2 * $length * $bits / 8.0);
            $data = \random_bytes($urandomLength);
            $unpackedData = 0;
            $unpackedBits = 0;
            for ($i = 0; $i < $urandomLength && $length > 0; ++$i) {
                // Unpack 8 bits
                $unpackedData = $unpackedData << 8 | \ord($data[$i]);
                $unpackedBits += 8;
                // While we have enough bits to select a character from the alphabet, keep
                // consuming the random data
                for (; $unpackedBits >= $bits && $length > 0; $unpackedBits -= $bits) {
                    $index = $unpackedData & (1 << $bits) - 1;
                    $unpackedData >>= $bits;
                    // Unfortunately, the alphabet size is not necessarily a power of two.
                    // Worst case, it is 2^k + 1, which means we need (k+1) bits and we
                    // have around a 50% chance of missing as k gets larger
                    if ($index < $alphabetSize) {
                        $ret .= $alphabet[$index];
                        --$length;
                    }
                }
            }
        }
        return new static($ret);
    }
    /**
     * @param int $offset
     */
    public function bytesAt($offset) : array
    {
        $str = $this->string[$offset] ?? '';
        return '' === $str ? [] : [\ord($str)];
    }
    /**
     * @param string ...$suffix
     */
    public function append(...$suffix) : parent
    {
        $str = clone $this;
        $str->string .= 1 >= \count($suffix) ? $suffix[0] ?? '' : \implode('', $suffix);
        return $str;
    }
    public function camel() : parent
    {
        $str = clone $this;
        $str->string = \lcfirst(\str_replace(' ', '', \ucwords(\preg_replace('/[^a-zA-Z0-9\\x7f-\\xff]++/', ' ', $this->string))));
        return $str;
    }
    /**
     * @param int $length
     */
    public function chunk($length = 1) : array
    {
        if (1 > $length) {
            throw new \RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException('The chunk length must be greater than zero.');
        }
        if ('' === $this->string) {
            return [];
        }
        $str = clone $this;
        $chunks = [];
        foreach (\str_split($this->string, $length) as $chunk) {
            $str->string = $chunk;
            $chunks[] = clone $str;
        }
        return $chunks;
    }
    public function endsWith($suffix) : bool
    {
        if ($suffix instanceof parent) {
            $suffix = $suffix->string;
        } elseif (\is_array($suffix) || $suffix instanceof \Traversable) {
            return parent::endsWith($suffix);
        } else {
            $suffix = (string) $suffix;
        }
        return '' !== $suffix && \strlen($this->string) >= \strlen($suffix) && 0 === \substr_compare($this->string, $suffix, -\strlen($suffix), null, $this->ignoreCase);
    }
    public function equalsTo($string) : bool
    {
        if ($string instanceof parent) {
            $string = $string->string;
        } elseif (\is_array($string) || $string instanceof \Traversable) {
            return parent::equalsTo($string);
        } else {
            $string = (string) $string;
        }
        if ('' !== $string && $this->ignoreCase) {
            return 0 === \strcasecmp($string, $this->string);
        }
        return $string === $this->string;
    }
    public function folded() : parent
    {
        $str = clone $this;
        $str->string = \strtolower($str->string);
        return $str;
    }
    /**
     * @param int $offset
     */
    public function indexOf($needle, $offset = 0) : ?int
    {
        if ($needle instanceof parent) {
            $needle = $needle->string;
        } elseif (\is_array($needle) || $needle instanceof \Traversable) {
            return parent::indexOf($needle, $offset);
        } else {
            $needle = (string) $needle;
        }
        if ('' === $needle) {
            return null;
        }
        $i = $this->ignoreCase ? \stripos($this->string, $needle, $offset) : \strpos($this->string, $needle, $offset);
        return \false === $i ? null : $i;
    }
    /**
     * @param int $offset
     */
    public function indexOfLast($needle, $offset = 0) : ?int
    {
        if ($needle instanceof parent) {
            $needle = $needle->string;
        } elseif (\is_array($needle) || $needle instanceof \Traversable) {
            return parent::indexOfLast($needle, $offset);
        } else {
            $needle = (string) $needle;
        }
        if ('' === $needle) {
            return null;
        }
        $i = $this->ignoreCase ? \strripos($this->string, $needle, $offset) : \strrpos($this->string, $needle, $offset);
        return \false === $i ? null : $i;
    }
    public function isUtf8() : bool
    {
        return '' === $this->string || \preg_match('//u', $this->string);
    }
    /**
     * @param mixed[] $strings
     * @param string|null $lastGlue
     */
    public function join($strings, $lastGlue = null) : parent
    {
        $str = clone $this;
        $tail = null !== $lastGlue && 1 < \count($strings) ? $lastGlue . \array_pop($strings) : '';
        $str->string = \implode($this->string, $strings) . $tail;
        return $str;
    }
    public function length() : int
    {
        return \strlen($this->string);
    }
    public function lower() : parent
    {
        $str = clone $this;
        $str->string = \strtolower($str->string);
        return $str;
    }
    /**
     * @param string $regexp
     * @param int $flags
     * @param int $offset
     */
    public function match($regexp, $flags = 0, $offset = 0) : array
    {
        $match = (\PREG_PATTERN_ORDER | \PREG_SET_ORDER) & $flags ? 'preg_match_all' : 'preg_match';
        if ($this->ignoreCase) {
            $regexp .= 'i';
        }
        \set_error_handler(static function ($t, $m) {
            throw new \RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException($m);
        });
        try {
            if (\false === $match($regexp, $this->string, $matches, $flags, $offset)) {
                $lastError = \preg_last_error();
                foreach (\get_defined_constants(\true)['pcre'] as $k => $v) {
                    if ($lastError === $v && '_ERROR' === \substr($k, -6)) {
                        throw new \RectorPrefix20211020\Symfony\Component\String\Exception\RuntimeException('Matching failed with ' . $k . '.');
                    }
                }
                throw new \RectorPrefix20211020\Symfony\Component\String\Exception\RuntimeException('Matching failed with unknown error code.');
            }
            \array_walk_recursive($matches, function (&$value) {
                if ($value === '') {
                    $value = null;
                }
            });
        } finally {
            \restore_error_handler();
        }
        return $matches;
    }
    /**
     * @param int $length
     * @param string $padStr
     */
    public function padBoth($length, $padStr = ' ') : parent
    {
        $str = clone $this;
        $str->string = \str_pad($this->string, $length, $padStr, \STR_PAD_BOTH);
        return $str;
    }
    /**
     * @param int $length
     * @param string $padStr
     */
    public function padEnd($length, $padStr = ' ') : parent
    {
        $str = clone $this;
        $str->string = \str_pad($this->string, $length, $padStr, \STR_PAD_RIGHT);
        return $str;
    }
    /**
     * @param int $length
     * @param string $padStr
     */
    public function padStart($length, $padStr = ' ') : parent
    {
        $str = clone $this;
        $str->string = \str_pad($this->string, $length, $padStr, \STR_PAD_LEFT);
        return $str;
    }
    /**
     * @param string ...$prefix
     */
    public function prepend(...$prefix) : parent
    {
        $str = clone $this;
        $str->string = (1 >= \count($prefix) ? $prefix[0] ?? '' : \implode('', $prefix)) . $str->string;
        return $str;
    }
    /**
     * @param string $from
     * @param string $to
     */
    public function replace($from, $to) : parent
    {
        $str = clone $this;
        if ('' !== $from) {
            $str->string = $this->ignoreCase ? \str_ireplace($from, $to, $this->string) : \str_replace($from, $to, $this->string);
        }
        return $str;
    }
    /**
     * @param string $fromRegexp
     */
    public function replaceMatches($fromRegexp, $to) : parent
    {
        if ($this->ignoreCase) {
            $fromRegexp .= 'i';
        }
        if (\is_array($to)) {
            if (!\is_callable($to)) {
                throw new \TypeError(\sprintf('Argument 2 passed to "%s::replaceMatches()" must be callable, array given.', static::class));
            }
            $replace = 'preg_replace_callback';
        } else {
            $replace = $to instanceof \Closure ? 'preg_replace_callback' : 'preg_replace';
        }
        \set_error_handler(static function ($t, $m) {
            throw new \RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException($m);
        });
        try {
            if (null === ($string = $replace($fromRegexp, $to, $this->string))) {
                $lastError = \preg_last_error();
                foreach (\get_defined_constants(\true)['pcre'] as $k => $v) {
                    if ($lastError === $v && '_ERROR' === \substr($k, -6)) {
                        throw new \RectorPrefix20211020\Symfony\Component\String\Exception\RuntimeException('Matching failed with ' . $k . '.');
                    }
                }
                throw new \RectorPrefix20211020\Symfony\Component\String\Exception\RuntimeException('Matching failed with unknown error code.');
            }
        } finally {
            \restore_error_handler();
        }
        $str = clone $this;
        $str->string = $string;
        return $str;
    }
    public function reverse() : parent
    {
        $str = clone $this;
        $str->string = \strrev($str->string);
        return $str;
    }
    /**
     * @param int $start
     * @param int|null $length
     */
    public function slice($start = 0, $length = null) : parent
    {
        $str = clone $this;
        $str->string = (string) \substr($this->string, $start, $length ?? \PHP_INT_MAX);
        return $str;
    }
    public function snake() : parent
    {
        $str = $this->camel()->title();
        $str->string = \strtolower(\preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\\d])([A-Z])/'], 'RectorPrefix20211020\\1_\\2', $str->string));
        return $str;
    }
    /**
     * @param string $replacement
     * @param int $start
     * @param int|null $length
     */
    public function splice($replacement, $start = 0, $length = null) : parent
    {
        $str = clone $this;
        $str->string = \substr_replace($this->string, $replacement, $start, $length ?? \PHP_INT_MAX);
        return $str;
    }
    /**
     * @param string $delimiter
     * @param int|null $limit
     * @param int|null $flags
     */
    public function split($delimiter, $limit = null, $flags = null) : array
    {
        if (1 > ($limit = $limit ?? \PHP_INT_MAX)) {
            throw new \RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException('Split limit must be a positive integer.');
        }
        if ('' === $delimiter) {
            throw new \RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException('Split delimiter is empty.');
        }
        if (null !== $flags) {
            return parent::split($delimiter, $limit, $flags);
        }
        $str = clone $this;
        $chunks = $this->ignoreCase ? \preg_split('{' . \preg_quote($delimiter) . '}iD', $this->string, $limit) : \explode($delimiter, $this->string, $limit);
        foreach ($chunks as &$chunk) {
            $str->string = $chunk;
            $chunk = clone $str;
        }
        return $chunks;
    }
    public function startsWith($prefix) : bool
    {
        if ($prefix instanceof parent) {
            $prefix = $prefix->string;
        } elseif (!\is_string($prefix)) {
            return parent::startsWith($prefix);
        }
        return '' !== $prefix && 0 === ($this->ignoreCase ? \strncasecmp($this->string, $prefix, \strlen($prefix)) : \strncmp($this->string, $prefix, \strlen($prefix)));
    }
    /**
     * @param bool $allWords
     */
    public function title($allWords = \false) : parent
    {
        $str = clone $this;
        $str->string = $allWords ? \ucwords($str->string) : \ucfirst($str->string);
        return $str;
    }
    /**
     * @param string|null $fromEncoding
     */
    public function toUnicodeString($fromEncoding = null) : \RectorPrefix20211020\Symfony\Component\String\UnicodeString
    {
        return new \RectorPrefix20211020\Symfony\Component\String\UnicodeString($this->toCodePointString($fromEncoding)->string);
    }
    /**
     * @param string|null $fromEncoding
     */
    public function toCodePointString($fromEncoding = null) : \RectorPrefix20211020\Symfony\Component\String\CodePointString
    {
        $u = new \RectorPrefix20211020\Symfony\Component\String\CodePointString();
        if (\in_array($fromEncoding, [null, 'utf8', 'utf-8', 'UTF8', 'UTF-8'], \true) && \preg_match('//u', $this->string)) {
            $u->string = $this->string;
            return $u;
        }
        \set_error_handler(static function ($t, $m) {
            throw new \RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException($m);
        });
        try {
            try {
                $validEncoding = \false !== \mb_detect_encoding($this->string, $fromEncoding ?? 'Windows-1252', \true);
            } catch (\RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException $e) {
                if (!\function_exists('iconv')) {
                    throw $e;
                }
                $u->string = \iconv($fromEncoding ?? 'Windows-1252', 'UTF-8', $this->string);
                return $u;
            }
        } finally {
            \restore_error_handler();
        }
        if (!$validEncoding) {
            throw new \RectorPrefix20211020\Symfony\Component\String\Exception\InvalidArgumentException(\sprintf('Invalid "%s" string.', $fromEncoding ?? 'Windows-1252'));
        }
        $u->string = \mb_convert_encoding($this->string, 'UTF-8', $fromEncoding ?? 'Windows-1252');
        return $u;
    }
    /**
     * @param string $chars
     */
    public function trim($chars = " \t\n\r\0\v\f") : parent
    {
        $str = clone $this;
        $str->string = \trim($str->string, $chars);
        return $str;
    }
    /**
     * @param string $chars
     */
    public function trimEnd($chars = " \t\n\r\0\v\f") : parent
    {
        $str = clone $this;
        $str->string = \rtrim($str->string, $chars);
        return $str;
    }
    /**
     * @param string $chars
     */
    public function trimStart($chars = " \t\n\r\0\v\f") : parent
    {
        $str = clone $this;
        $str->string = \ltrim($str->string, $chars);
        return $str;
    }
    public function upper() : parent
    {
        $str = clone $this;
        $str->string = \strtoupper($str->string);
        return $str;
    }
    /**
     * @param bool $ignoreAnsiDecoration
     */
    public function width($ignoreAnsiDecoration = \true) : int
    {
        $string = \preg_match('//u', $this->string) ? $this->string : \preg_replace('/[\\x80-\\xFF]/', '?', $this->string);
        return (new \RectorPrefix20211020\Symfony\Component\String\CodePointString($string))->width($ignoreAnsiDecoration);
    }
}
