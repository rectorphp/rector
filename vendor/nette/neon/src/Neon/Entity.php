<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Nette\Neon;

/**
 * Representation of NEON entity 'foo(bar=1)'
 */
final class Entity extends \stdClass
{
    /**
     * @var mixed
     */
    public $value;
    /**
     * @var mixed[]
     */
    public $attributes = [];
    /**
     * @param mixed $value
     */
    public function __construct($value, array $attributes = [])
    {
        $this->value = $value;
        /** @var mixed[] */
        $this->attributes = $attributes;
    }
    /** @param  mixed[]  $properties */
    public static function __set_state(array $properties)
    {
        return new self($properties['value'], $properties['attributes']);
    }
}
