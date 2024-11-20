<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
class ArrayTypeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    public \PHPStan\PhpDocParser\Ast\Type\TypeNode $type;
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\TypeNode $type)
    {
        $this->type = $type;
    }
    public function __toString() : string
    {
        if ($this->type instanceof \PHPStan\PhpDocParser\Ast\Type\CallableTypeNode || $this->type instanceof \PHPStan\PhpDocParser\Ast\Type\ConstTypeNode || $this->type instanceof \PHPStan\PhpDocParser\Ast\Type\NullableTypeNode) {
            return '(' . $this->type . ')[]';
        }
        return $this->type . '[]';
    }
}
