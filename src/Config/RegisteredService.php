<?php

declare (strict_types=1);
namespace Rector\Config;

final class RegisteredService
{
    /**
     * @readonly
     */
    private string $className;
    /**
     * @readonly
     */
    private ?string $alias;
    /**
     * @readonly
     */
    private ?string $tag;
    public function __construct(string $className, ?string $alias, ?string $tag)
    {
        $this->className = $className;
        $this->alias = $alias;
        $this->tag = $tag;
    }
    public function getClassName() : string
    {
        return $this->className;
    }
    public function getAlias() : ?string
    {
        return $this->alias;
    }
    public function getTag() : ?string
    {
        return $this->tag;
    }
}
