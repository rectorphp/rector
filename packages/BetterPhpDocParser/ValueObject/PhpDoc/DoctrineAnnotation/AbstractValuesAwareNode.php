<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation;

use RectorPrefix20220418\Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\Util\StringUtils;
abstract class AbstractValuesAwareNode implements \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    /**
     * @var string
     * @see https://regex101.com/r/H6JjOG/3
     */
    private const UNQUOTED_VALUE_REGEX = '#^("|\')(?<content>.*?)("|\')$#';
    /**
     * @var bool
     */
    protected $hasChanged = \false;
    /**
     * @var mixed[]
     */
    private $originalValues = [];
    /**
     * @var array<(string | int), mixed>
     */
    public $values = [];
    /**
     * @var string|null
     */
    protected $originalContent;
    /**
     * @var string|null
     */
    protected $silentKey;
    /**
     * @param array<string|int, mixed> $values Must be public so node traverser can go through them
     */
    public function __construct(array $values = [], ?string $originalContent = null, ?string $silentKey = null)
    {
        $this->values = $values;
        $this->originalContent = $originalContent;
        $this->silentKey = $silentKey;
        $this->originalValues = $values;
    }
    public function removeValue(string $key) : void
    {
        $quotedKey = '"' . $key . '"';
        // isset?
        if (!isset($this->values[$key]) && !isset($this->values[$quotedKey])) {
            return;
        }
        unset($this->values[$key]);
        unset($this->values[$quotedKey]);
        // invoke reprint
        $this->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE, null);
    }
    /**
     * @return mixed[]
     */
    public function getValues() : array
    {
        return $this->values;
    }
    /**
     * @return mixed|Node|null
     * @param int|string $key
     */
    public function getValue($key)
    {
        // to allow false as default
        if (!\array_key_exists($key, $this->values)) {
            return null;
        }
        return $this->values[$key];
    }
    /**
     * @param mixed $value
     */
    public function changeValue(string $key, $value) : void
    {
        // is quoted?
        if (isset($this->values[$key]) && \is_string($this->values[$key]) && \Rector\Core\Util\StringUtils::isMatch($this->values[$key], self::UNQUOTED_VALUE_REGEX)) {
            $value = '"' . $value . '"';
        }
        $this->values[$key] = $value;
        // invoke reprint
        $this->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE, null);
    }
    /**
     * @return mixed|null
     * @param int|string $key
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
     * @param mixed $value
     */
    public function changeSilentValue($value) : void
    {
        // is quoted?
        if (\Rector\Core\Util\StringUtils::isMatch($this->values[0], self::UNQUOTED_VALUE_REGEX)) {
            $value = '"' . $value . '"';
        }
        $this->values[0] = $value;
        $this->hasChanged = \true;
        // invoke reprint
        $this->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE, null);
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
     * Useful for attributes
     * @return array<int|string, mixed>
     */
    public function getValuesWithExplicitSilentAndWithoutQuotes() : array
    {
        $explicitKeysValues = [];
        foreach (\array_keys($this->values) as $key) {
            $valueWithoutQuotes = $this->getValueWithoutQuotes($key);
            if (\is_int($key) && $this->silentKey !== null) {
                $explicitKeysValues[$this->silentKey] = $valueWithoutQuotes;
            } else {
                $explicitKeysValues[$this->removeQuotes($key)] = $valueWithoutQuotes;
            }
        }
        return $explicitKeysValues;
    }
    public function markAsChanged() : void
    {
        $this->hasChanged = \true;
    }
    /**
     * @return mixed[]
     */
    public function getOriginalValues() : array
    {
        return $this->originalValues;
    }
    /**
     * @param mixed|string $value
     * @return mixed|string
     */
    protected function removeQuotes($value)
    {
        if (\is_array($value)) {
            return $this->removeQuotesFromArray($value);
        }
        if (!\is_string($value)) {
            return $value;
        }
        $matches = \RectorPrefix20220418\Nette\Utils\Strings::match($value, self::UNQUOTED_VALUE_REGEX);
        if ($matches === null) {
            return $value;
        }
        return $matches['content'];
    }
    /**
     * @param mixed[] $values
     * @return array<int|string, mixed>
     */
    protected function removeQuotesFromArray(array $values) : array
    {
        $unquotedArray = [];
        foreach ($values as $key => $value) {
            $unquotedKey = $this->removeQuotes($key);
            $unquotedValue = $this->removeQuotes($value);
            $unquotedArray[$unquotedKey] = $unquotedValue;
        }
        return $unquotedArray;
    }
    /**
     * @param mixed[] $values
     */
    protected function printValuesContent(array $values) : string
    {
        $itemContents = '';
        \end($values);
        $lastItemKey = \key($values);
        foreach ($values as $key => $value) {
            if (\is_int($key)) {
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
    /**
     * @param mixed $value
     */
    private function stringifyValue($value) : string
    {
        // @todo resolve original casing
        if ($value === \false) {
            return 'false';
        }
        if ($value === \true) {
            return 'true';
        }
        if (\is_int($value)) {
            return (string) $value;
        }
        if (\is_float($value)) {
            return (string) $value;
        }
        if (\is_array($value)) {
            return $this->printValuesContent($value);
        }
        return (string) $value;
    }
}
