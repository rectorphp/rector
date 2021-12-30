<?php

declare (strict_types=1);
namespace Rector\Caching\ValueObject;

/**
 * Inspired by
 * https://github.com/phpstan/phpstan-src/commit/eeae2da7999b2e8b7b04542c6175d46f80c6d0b9#diff-6dc14f6222bf150e6840ca44a7126653052a1cedc6a149b4e5c1e1a2c80eacdc
 */
final class CacheItem
{
    /**
     * @readonly
     * @var string
     */
    private $variableKey;
    /**
     * @var mixed
     */
    private $data;
    /**
     * @param mixed $data
     */
    public function __construct(string $variableKey, $data)
    {
        $this->variableKey = $variableKey;
        $this->data = $data;
    }
    /**
     * @param mixed[] $properties
     */
    public static function __set_state(array $properties) : self
    {
        return new self($properties['variableKey'], $properties['data']);
    }
    public function isVariableKeyValid(string $variableKey) : bool
    {
        return $this->variableKey === $variableKey;
    }
    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
