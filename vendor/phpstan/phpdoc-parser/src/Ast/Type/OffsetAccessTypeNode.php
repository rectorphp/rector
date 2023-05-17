<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
class OffsetAccessTypeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    /** @var TypeNode */
    public $type;
    /** @var TypeNode */
    public $offset;
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\TypeNode $type, \PHPStan\PhpDocParser\Ast\Type\TypeNode $offset)
    {
        $this->type = $type;
        $this->offset = $offset;
    }
    public function __toString() : string
    {
        if ($this->type instanceof \PHPStan\PhpDocParser\Ast\Type\CallableTypeNode || $this->type instanceof \PHPStan\PhpDocParser\Ast\Type\ConstTypeNode || $this->type instanceof \PHPStan\PhpDocParser\Ast\Type\NullableTypeNode) {
            return '(' . $this->type . ')[' . $this->offset . ']';
        }
        return $this->type . '[' . $this->offset . ']';
    }
}
