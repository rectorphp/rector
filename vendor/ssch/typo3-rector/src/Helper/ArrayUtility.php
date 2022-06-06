<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use InvalidArgumentException;
final class ArrayUtility
{
    /**
     * @return string[]
     */
    public static function trimExplode(string $delimiter, string $string, bool $removeEmptyValues = \false, int $limit = 0) : array
    {
        if ('' === $delimiter) {
            throw new \InvalidArgumentException('Please define a correct delimiter');
        }
        $result = \explode($delimiter, $string);
        if ($removeEmptyValues) {
            $temp = [];
            foreach ($result as $value) {
                if ('' !== \trim($value)) {
                    $temp[] = $value;
                }
            }
            $result = $temp;
        }
        if ($limit > 0 && \count($result) > $limit) {
            $lastElements = \array_splice($result, $limit - 1);
            $result[] = \implode($delimiter, $lastElements);
        } elseif ($limit < 0) {
            $result = \array_slice($result, 0, $limit);
        }
        return \array_map('trim', $result);
    }
}
