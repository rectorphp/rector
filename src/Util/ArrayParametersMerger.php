<?php

declare (strict_types=1);
namespace Rector\Core\Util;

final class ArrayParametersMerger
{
    /**
     * Merges configurations. Left has higher priority than right one.
     *
     * @autor David Grudl (https://davidgrudl.com)
     * @source https://github.com/nette/di/blob/8eb90721a131262f17663e50aee0032a62d0ef08/src/DI/Config/Helpers.php#L31
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function merge($left, $right)
    {
        if (\is_array($left) && \is_array($right)) {
            return $this->mergeLeftToRightWithCallable($left, $right, function ($leftValue, $rightValue) {
                return $this->merge($leftValue, $rightValue);
            });
        }
        if ($left !== null) {
            return $left;
        }
        if (!\is_array($right)) {
            return $left;
        }
        return $right;
    }
    /**
     * The same as above, just with the case if both values being non-array, it will combined them to array:
     *
     * $this->mergeWithCombine(1, 2); // [1, 2]
     * @param mixed $left
     * @param mixed $right
     * @return mixed
     */
    public function mergeWithCombine($left, $right)
    {
        if (\is_array($left) && \is_array($right)) {
            return $this->mergeLeftToRightWithCallable($left, $right, function ($leftValue, $rightValue) {
                return $this->mergeWithCombine($leftValue, $rightValue);
            });
        }
        if ($left === null && \is_array($right)) {
            return $right;
        }
        if (!empty($right) && (array) $left !== (array) $right) {
            return $this->mergeWithCombine((array) $right, (array) $left);
        }
        return $left;
    }
    /**
     * @param array<int|string, mixed> $left
     * @param array<int|string, mixed> $right
     * @return mixed[]
     */
    private function mergeLeftToRightWithCallable(array $left, array $right, callable $mergeCallback) : array
    {
        foreach ($left as $key => $val) {
            if (\is_int($key)) {
                // prevent duplicated values in unindexed arrays
                if (!\in_array($val, $right, \true)) {
                    $right[] = $val;
                }
            } else {
                if (isset($right[$key])) {
                    $val = $mergeCallback($val, $right[$key]);
                }
                $right[$key] = $val;
            }
        }
        return $right;
    }
}
