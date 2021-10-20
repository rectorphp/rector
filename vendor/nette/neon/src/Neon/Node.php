<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Neon;

/** @internal */
abstract class Node
{
    /** @var ?int */
    public $startPos;
    /** @var ?int */
    public $endPos;
    /** @return mixed */
    public abstract function toValue();
    public abstract function toString() : string;
    /** @return self[] */
    public function getSubNodes() : array
    {
        return [];
    }
}
