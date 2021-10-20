<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Neon;

/** @internal */
final class Token
{
    public const STRING = 1;
    public const LITERAL = 2;
    public const CHAR = 0;
    public const COMMENT = 3;
    public const NEWLINE = 4;
    public const WHITESPACE = 5;
    /** @var string */
    public $value;
    /** @var int */
    public $offset;
    /** @var int|string */
    public $type;
    public function __construct(string $value, int $offset, $type)
    {
        $this->value = $value;
        $this->offset = $offset;
        $this->type = $type;
    }
}
