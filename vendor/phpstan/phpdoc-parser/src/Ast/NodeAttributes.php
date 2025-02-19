<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast;

use function array_key_exists;
trait NodeAttributes
{
    /** @var array<string, mixed> */
    private array $attributes = [];
    /**
     * @param mixed $value
     */
    public function setAttribute(string $key, $value) : void
    {
        if ($value === null) {
            unset($this->attributes[$key]);
            return;
        }
        $this->attributes[$key] = $value;
    }
    public function hasAttribute(string $key) : bool
    {
        return array_key_exists($key, $this->attributes);
    }
    /**
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
