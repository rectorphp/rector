<?php

declare (strict_types=1);
namespace Rector\Util;

final class ArrayChecker
{
    /**
     * @param mixed[] $elements
     * @param callable(mixed $element): bool $callable
     */
    public function doesExist(array $elements, callable $callable) : bool
    {
        foreach ($elements as $element) {
            $isFound = $callable($element);
            if ($isFound) {
                return \true;
            }
        }
        return \false;
    }
}
