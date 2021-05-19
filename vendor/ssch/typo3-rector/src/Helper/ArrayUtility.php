<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use UnexpectedValueException;
final class ArrayUtility
{
    /**
     * @return string[]
     */
    public static function trimExplode(string $delimiter, string $string, bool $removeEmptyValues = \false, int $limit = 0) : array
    {
        $result = \explode($delimiter, $string);
        if (\false === $result) {
            throw new \UnexpectedValueException(\sprintf('String %s could not be exploded by %s', $string, $delimiter));
        }
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
