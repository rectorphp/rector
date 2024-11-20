<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

use PhpParser\Node\Expr\Array_;
final class ExtensionKeyAndConfiguration
{
    /**
     * @readonly
     */
    private string $key;
    /**
     * @readonly
     */
    private Array_ $array;
    public function __construct(string $key, Array_ $array)
    {
        $this->key = $key;
        $this->array = $array;
    }
    public function getKey() : string
    {
        return $this->key;
    }
    public function getArray() : Array_
    {
        return $this->array;
    }
}
