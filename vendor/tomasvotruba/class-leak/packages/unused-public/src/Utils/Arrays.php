<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\Utils;

final class Arrays
{
    /**
     * @param mixed[] $array
     * @return mixed[]
     *
     * @license nette/utils, where exactly copied from
     */
    public static function flatten(array $array): array
    {
        $result = [];
        $callback = static function ($value) use (&$result): void {
            $result[] = $value;
        };
        array_walk_recursive($array, $callback);
        return $result;
    }
}
