<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\ConstExpr;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
class ConstExprNullNode implements \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
{
    use NodeAttributes;
    public function __toString(): string
    {
        return 'null';
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
