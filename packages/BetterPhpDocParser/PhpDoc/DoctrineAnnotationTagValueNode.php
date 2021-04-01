<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDoc;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

final class DoctrineAnnotationTagValueNode extends AbstractTagValueNode implements PhpDocTagValueNode
{
    /**
     * @var string
     * @see https://regex101.com/r/H6JjOG/1
     */
    private const UNQUOTED_VALUE_REGEX = '#"(?<content>.*?)"#';

    /**
     * @var string
     */
    private $annotationClass;

    /**
     * @var string|null
     */
    private $docContent;

    /**
     * @var array<mixed, mixed>
     */
    private $values = [];

    /**
     * @var bool
     */
    private $hasChanged = false;

    /**
     * @var string|null
     */
    private $silentKey;

    /**
     * @param array<mixed, mixed> $values
     */
    public function __construct(
        // values
        string $annotationClass,
        ?string $docContent = null,
        array $values,
        ?string $silentKey = null
    ) {
        $this->annotationClass = $annotationClass;
        $this->docContent = $docContent;
        $this->values = $values;
        $this->silentKey = $silentKey;
        $this->hasChanged = true;

        parent::__construct(
            $values,
            $docContent
        );
    }

    public function __toString(): string
    {
        if (! $this->hasChanged) {
            if ($this->docContent === null) {
                return '';
            }

            return $this->docContent;
        }

        if ($this->values === []) {
            if ($this->docContent === '()') {
                // empty brackets
                return $this->docContent;
            }

            return '';
        }

        $itemContents = $this->printItemContent($this->values);

        // without modifications, @todo split into items if needed
        return sprintf(
            '(%s%s%s)',
            $this->tagValueNodeConfiguration->hasNewlineBeforeClosing() ? PHP_EOL : '',
            $itemContents,
            $this->tagValueNodeConfiguration->hasNewlineAfterOpening() ? PHP_EOL : ''
        );
    }

    public function getAnnotationClass(): string
    {
        return $this->annotationClass;
    }

    public function getDocContent(): ?string
    {
        return $this->docContent;
    }

    /**
     * @return mixed[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Useful for attributes
     * @return array<string, mixed>
     */
    public function getValuesWithExplicitSilentAndWithoutQuotes(): array
    {
        $explicitKeysValues = [];

        foreach (array_keys($this->values) as $key) {
            $valueWithoutQuotes = $this->getValueWithoutQuotes($key);

            if (is_int($key) && $this->silentKey !== null) {
                $explicitKeysValues[$this->silentKey] = $valueWithoutQuotes;
            } else {
                $explicitKeysValues[$key] = $valueWithoutQuotes;
            }
        }

        return $explicitKeysValues;
    }

    /**
     * @return mixed|null
     */
    public function getSilentValue()
    {
        $value = $this->values[0] ?? null;
        if ($value === null) {
            return null;
        }

        return $this->removeQuotes($value);
    }

    /**
     * @param mixed $value
     */
    public function changeSilentValue($value): void
    {
        // is quoted?
        $isQuoted = (bool) Strings::match($this->items[0], self::UNQUOTED_VALUE_REGEX);
        if ($isQuoted) {
            $value = '"' . $value . '"';
        }

        $this->values[0] = $value;
        $this->items[0] = $value;
        $this->hasChanged = true;
    }

    /**
     * @todo should be type of \PHPStan\PhpDocParser\Ast\Node
     * @param string|int $key
     * @return mixed|null
     */
    public function getValue($key)
    {
        // to allow false as default
        if (! array_key_exists($key, $this->values)) {
            return null;
        }

        return $this->values[$key];
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
        $this->items[$key] = $value;
        $this->hasChanged = true;
    }

    /**
     * @param string|int $key
     * @return mixed|null
     */
    public function getValueWithoutQuotes($key)
    {
        $value = $this->getValue($key);
        if ($value === null) {
            return null;
        }

        return $this->removeQuotes($value);
    }

    /**
     * @param mixed|string $value
     * @return mixed|string
     */
    private function removeQuotes($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        $matches = Strings::match($value, self::UNQUOTED_VALUE_REGEX);
        if ($matches === null) {
            return $value;
        }

        return $matches['content'];
    }

    /**
     * @param mixed $value
     */
    private function stringifyValue($value): string
    {
        // @todo resolve original casing
        if ($value === false) {
            return 'false';
        }

        if ($value === true) {
            return 'true';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            return $this->printItemContent($value);
        }

        return (string) $value;
    }

    /**
     * @param mixed[] $values
     */
    private function printItemContent(array $values): string
    {
        $itemContents = '';
        $lastItemKey = array_key_last($values);

        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $itemContents .= $this->stringifyValue($value);
            } else {
                $itemContents .= $key . '=' . $this->stringifyValue($value);
            }

            if ($lastItemKey !== $key) {
                $itemContents .= ', ';
            }
        }

        return $itemContents;
    }
}
