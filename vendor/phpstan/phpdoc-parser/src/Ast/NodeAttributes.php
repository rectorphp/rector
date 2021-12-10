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
    public function setAttribute($key, $value) : void
    {
        $this->attributes[$key] = $value;
    }
    /**
     * @param string $key
     */
    public function hasAttribute($key) : bool
    {
        return \array_key_exists($key, $this->attributes);
    }
    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ($this->hasAttribute($key)) {
            return $this->attributes[$key];
        }
        return null;
    }
}
