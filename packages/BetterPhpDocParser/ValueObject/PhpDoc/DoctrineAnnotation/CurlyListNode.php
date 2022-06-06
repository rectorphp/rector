<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation;

use Stringable;
final class CurlyListNode extends \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\AbstractValuesAwareNode
{
    public function __toString() : string
    {
        return $this->implode($this->values);
    }
    /**
     * @param mixed $value
     */
    private function stringifyValue($value) : string
    {
        if ($value === \false) {
            return 'false';
        }
        if ($value === \true) {
            return 'true';
        }
        if (\is_array($value)) {
            return $this->implode($value);
        }
        return (string) $value;
    }
    /**
     * @param mixed[] $array
     */
    private function implode(array $array) : string
    {
        $itemContents = '';
        \end($array);
        $lastItemKey = \key($array);
        foreach ($array as $key => $value) {
            if (\is_int($key)) {
                $itemContents .= $this->stringifyValue($value);
            } else {
                $itemContents .= $key . '=' . $this->stringifyValue($value);
            }
            if ($lastItemKey !== $key) {
                $itemContents .= ', ';
            }
        }
        return '{' . $itemContents . '}';
    }
}
