<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function array_map;
use function implode;
class IntersectionTypeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    /** @var TypeNode[] */
    public array $types;
    /**
     * @param TypeNode[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }
    public function __toString(): string
    {
        return '(' . implode(' & ', array_map(static function (\PHPStan\PhpDocParser\Ast\Type\TypeNode $type): string {
            if ($type instanceof \PHPStan\PhpDocParser\Ast\Type\NullableTypeNode) {
                return '(' . $type . ')';
            }
            return (string) $type;
        }, $this->types)) . ')';
    }
    /**
     * @param array<string, mixed> $properties
     */
    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['types']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }
        return $instance;
    }
}
