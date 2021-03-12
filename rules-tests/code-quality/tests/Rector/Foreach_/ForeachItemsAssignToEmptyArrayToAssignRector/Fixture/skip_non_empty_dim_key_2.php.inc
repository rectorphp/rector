<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class SkipNonEmptyDimKey2
{
    public function mapWithKeys(array $array, callable $fn): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            foreach (self::assertIterable($fn($value, $key)) as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }

    public static function assertIterable($traversable)
    {
        if (is_iterable($traversable)) {
            return $traversable;
        }

        throw new \UnexpectedValueException('Traversable expected');
    }
}
