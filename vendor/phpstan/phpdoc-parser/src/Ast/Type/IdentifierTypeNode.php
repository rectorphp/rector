<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
class IdentifierTypeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    public string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function __toString(): string
    {
        return $this->name;
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
