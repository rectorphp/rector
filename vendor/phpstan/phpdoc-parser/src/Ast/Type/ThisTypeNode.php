<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
class ThisTypeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    public function __toString() : string
    {
        return '$this';
    }
}
