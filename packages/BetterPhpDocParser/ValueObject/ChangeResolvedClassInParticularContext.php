<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject;

final class ChangeResolvedClassInParticularContext
{
    /**
     * @var string
     */
    private $tag;
    /**
     * @var string
     */
    private $value;
    /**
     * @var string
     */
    private $resolvedClass;
    public function __construct(string $tag, string $value, string $resolvedClass)
    {
        $this->tag = $tag;
        $this->value = $value;
        $this->resolvedClass = $resolvedClass;
    }
    public function getTag() : string
    {
        return $this->tag;
    }
    public function getValue() : string
    {
        return $this->value;
    }
    public function getResolvedClass() : string
    {
        return $this->resolvedClass;
    }
}
