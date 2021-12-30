<?php

declare (strict_types=1);
namespace Rector\Order\ValueObject;

final class SortedClassMethodsAndOriginalClassMethods
{
    /**
     * @var array<int, string>
     * @readonly
     */
    private $sortedClassMethods;
    /**
     * @var array<int, string>
     * @readonly
     */
    private $originalClassMethods;
    /**
     * @param array<int, string> $sortedClassMethods
     * @param array<int, string> $originalClassMethods
     */
    public function __construct(array $sortedClassMethods, array $originalClassMethods)
    {
        $this->sortedClassMethods = $sortedClassMethods;
        $this->originalClassMethods = $originalClassMethods;
    }
    /**
     * @return array<int, string>
     */
    public function getSortedClassMethods() : array
    {
        return $this->sortedClassMethods;
    }
    /**
     * @return array<int, string>
     */
    public function getOriginalClassMethods() : array
    {
        return $this->originalClassMethods;
    }
    public function hasOrderChanged() : bool
    {
        return $this->sortedClassMethods !== $this->originalClassMethods;
    }
    public function hasOrderSame() : bool
    {
        $sortedClassMethodValues = \array_values($this->sortedClassMethods);
        $originalClassMethodValues = \array_values($this->originalClassMethods);
        return $sortedClassMethodValues === $originalClassMethodValues;
    }
}
