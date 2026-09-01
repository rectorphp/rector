<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\ClassLeak\ValueObject;

final class ClassNames
{
    /**
     * @readonly
     */
    private string $className;
    /**
     * @readonly
     */
    private bool $hasParentClassOrInterface;
    /**
     * @var string[]
     * @readonly
     */
    private array $attributes;
    /**
     * @param string[] $attributes
     */
    public function __construct(string $className, bool $hasParentClassOrInterface, array $attributes)
    {
        $this->className = $className;
        $this->hasParentClassOrInterface = $hasParentClassOrInterface;
        $this->attributes = $attributes;
    }
    public function getClassName(): string
    {
        return $this->className;
    }
    public function hasParentClassOrInterface(): bool
    {
        return $this->hasParentClassOrInterface;
    }
    /**
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
