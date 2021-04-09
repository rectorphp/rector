<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation;

final class CurlyListNode extends AbstractValuesAwareNode
{
    public function __toString(): string
    {
        $itemContents = '';
        $lastItemKey = array_key_last($this->values);

        foreach ($this->values as $key => $value) {
            if (is_int($key)) {
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

    /**
     * @param mixed $value
     */
    private function stringifyValue($value): string
    {
        if ($value === false) {
            return 'false';
        }

        if ($value === true) {
            return 'true';
        }

        return (string) $value;
    }
}
