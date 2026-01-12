<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
class ThisTypeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    public function __toString(): string
    {
        return '$this';
    }
    /**
     * @param array<string, mixed> $properties
     */
    public static function __set_state(array $properties): self
    {
        $instance = new self();
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }
        return $instance;
    }
}
