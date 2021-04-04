<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;

abstract class AbstractValuesAwareNode implements Node
{
    use NodeAttributes;

    /**
     * @var string
     * @see https://regex101.com/r/H6JjOG/1
     */
    private const UNQUOTED_VALUE_REGEX = '#"(?<content>.*?)"#';

    /**
     * @var mixed[]
     */
    private $values = [];

    /**
     * @var bool
     */
    private $hasChanged = false;

    /**
     * @param mixed[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return mixed[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param mixed $value
     */
    public function changeValue(string $key, $value): void
    {
        // is quoted?
        $isQuoted = (bool) Strings::match($this->values[$key], self::UNQUOTED_VALUE_REGEX);
        if ($isQuoted) {
            $value = '"' . $value . '"';
        }

        $this->values[$key] = $value;
        $this->hasChanged = true;
    }

    public function printWithWrapper(string $leftWrapChar, string $rightWrapChar): string
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

        return $leftWrapChar . $itemContents . $rightWrapChar;
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
