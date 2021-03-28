<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDoc;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
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
     * @var string
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
        // printer services
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        // values
        string $annotationClass,
        string $docContent,
        array $values,
        ?string $silentKey = null
    ) {
        $this->annotationClass = $annotationClass;
        $this->docContent = $docContent;
        $this->values = $values;

        parent::__construct(
            $arrayPartPhpDocTagPrinter,
            $tagValueNodePrinter,
            $values,
            $docContent
        );
        $this->silentKey = $silentKey;
    }

    public function __toString(): string
    {
        if ($this->hasChanged === false) {
            return $this->docContent;
        }

        if ($this->values === []) {
            return '';
        }

        return parent::__toString();

        // @todo which to use?

        $itemContents = '';
        $lastItemKey = array_key_last($this->values);

        foreach ($this->values as $key => $value) {
            if (is_int($key)) {
                $itemContents .= '"' . $value . '"';
            } else {
                $itemContents .= $key . '=' . $value;
            }

            if ($lastItemKey !== $key) {
                $itemContents .= ', ';
            }
        }

        // without modifications, @todo split into items if needed
        return '(' . $itemContents . ')';
    }

    public function getAnnotationClass(): string
    {
        return $this->annotationClass;
    }

    public function getDocContent(): string
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

        foreach ($this->values as $key => $value) {
            $valueWithoutQuotes = $this->getValueWithoutQuotes($key);

            if (is_int($key)) {
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
        return $this->values[$key] ?? null;
    }

    /**
     * @param mixed $value
     */
    public function changeValue(string $key, $value): void
    {
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
}
