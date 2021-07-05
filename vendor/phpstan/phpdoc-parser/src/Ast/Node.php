<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast;

interface Node
{
    public function __toString() : string;
    /**
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute(string $key, $value) : void;
    public function hasAttribute(string $key) : bool;
    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key);
}
