<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;

final class CurlyListNode implements Node
{
    use NodeAttributes;

    /**
     * @var mixed[]
     */
    private $items = [];

    /**
     * @param mixed[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function __toString(): string
    {
        $itemContents = '';
        $lastItemKey = array_key_last($this->items);

        foreach ($this->items as $key => $value) {
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
