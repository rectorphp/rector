<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast;

trait NodeAttributes
{
    /** @var array<string, mixed> */
    private $attributes = [];
    /**
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute(string $key, $value) : void
    {
        $this->attributes[$key] = $value;
    }
    public function hasAttribute(string $key) : bool
    {
        return \array_key_exists($key, $this->attributes);
    }
    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        if ($this->hasAttribute($key)) {
            return $this->attributes[$key];
        }
        return null;
    }
}
