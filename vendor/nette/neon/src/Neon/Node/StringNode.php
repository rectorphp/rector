<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Neon\Node;

use RectorPrefix20211020\Nette;
use RectorPrefix20211020\Nette\Neon\Node;
/** @internal */
final class StringNode extends \RectorPrefix20211020\Nette\Neon\Node
{
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
    public function toString() : string
    {
        $res = \json_encode($this->value, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        if ($res === \false) {
            throw new \RectorPrefix20211020\Nette\Neon\Exception('Invalid UTF-8 sequence: ' . $this->value);
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
