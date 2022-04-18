<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220418\Nette\Neon\Node;

use RectorPrefix20220418\Nette;
use RectorPrefix20220418\Nette\Neon\Node;
/** @internal */
final class StringNode extends \RectorPrefix20220418\Nette\Neon\Node
{
    private const ESCAPE_SEQUENCES = ['t' => "\t", 'n' => "\n", 'r' => "\r", 'f' => "\f", 'b' => "\10", '"' => '"', '\\' => '\\', '/' => '/', '_' => " "];
    /** @var string */
    public $value;
    public function __construct(string $value, int $pos = null)
    {
        $this->value = $value;
        $this->startPos = $this->endPos = $pos;
    }
    public function toValue() : string
    {
        return $this->value;
    }
    public static function parse(string $s) : string
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
        if ($s[0] === "'") {
            return $res;
        }
        return \preg_replace_callback('#\\\\(?:ud[89ab][0-9a-f]{2}\\\\ud[c-f][0-9a-f]{2}|u[0-9a-f]{4}|x[0-9a-f]{2}|.)#i', function (array $m) : string {
            $sq = $m[0];
            if (isset(self::ESCAPE_SEQUENCES[$sq[1]])) {
                return self::ESCAPE_SEQUENCES[$sq[1]];
            } elseif ($sq[1] === 'u' && \strlen($sq) >= 6) {
                if (($res = \json_decode('"' . $sq . '"')) !== null) {
                    return $res;
                }
                throw new \RectorPrefix20220418\Nette\Neon\Exception("Invalid UTF-8 sequence {$sq}");
            } elseif ($sq[1] === 'x' && \strlen($sq) === 4) {
                \trigger_error("Neon: '{$sq}' is deprecated, use '\\uXXXX' instead.", \E_USER_DEPRECATED);
                return \chr(\hexdec(\substr($sq, 2)));
            } else {
                throw new \RectorPrefix20220418\Nette\Neon\Exception("Invalid escaping sequence {$sq}");
            }
        }, $res);
    }
    public function toString() : string
    {
        $res = \json_encode($this->value, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        if ($res === \false) {
            throw new \RectorPrefix20220418\Nette\Neon\Exception('Invalid UTF-8 sequence: ' . $this->value);
        }
        if (\strpos($this->value, "\n") !== \false) {
            $res = \preg_replace_callback('#[^\\\\]|\\\\(.)#s', function ($m) {
                return ['n' => "\n\t", 't' => "\t", '"' => '"'][$m[1] ?? ''] ?? $m[0];
            }, $res);
            $res = '"""' . "\n\t" . \substr($res, 1, -1) . "\n" . '"""';
        }
        return $res;
    }
}
