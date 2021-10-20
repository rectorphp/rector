<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Neon\Node;

use RectorPrefix20211020\Nette\Neon\Node;
/** @internal */
final class LiteralNode extends \RectorPrefix20211020\Nette\Neon\Node
{
    /** @var mixed */
    public $value;
    public function __construct($value, int $pos = null)
    {
        $this->value = $value;
        $this->startPos = $this->endPos = $pos;
    }
    public function toValue()
    {
        return $this->value;
    }
    public function toString() : string
    {
        if ($this->value instanceof \DateTimeInterface) {
            return $this->value->format('Y-m-d H:i:s O');
        } elseif (\is_string($this->value)) {
            return $this->value;
        } elseif (\is_float($this->value)) {
            $res = \json_encode($this->value);
            return \strpos($res, '.') === \false ? $res . '.0' : $res;
        } elseif (\is_int($this->value) || \is_bool($this->value) || $this->value === null) {
            return \json_encode($this->value);
        } else {
            throw new \LogicException();
        }
    }
}
