<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function count;
use function explode;
/**
 * @internal
 */
trait NameNodeTrait
{
    use NodeAttributes;
    /** The name without a leading backslash, e.g. Foo\Bar */
    public string $name;
    public function toString(): string
    {
        return $this->name;
    }
    public function getParts(): array
    {
        return explode('\\', $this->name);
    }
    public function getFirst(): string
    {
        return $this->getParts()[0];
    }
    public function getLast(): string
    {
        $parts = $this->getParts();
        return $parts[count($parts) - 1];
    }
    public function __toString(): string
    {
        return $this->toCodeString();
    }
    /**
     * @param array<string, mixed> $properties
     */
    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['name']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }
        return $instance;
    }
}
