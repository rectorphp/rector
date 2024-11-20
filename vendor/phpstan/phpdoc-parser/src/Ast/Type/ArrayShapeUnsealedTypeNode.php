<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ArrayShapeUnsealedTypeNode implements Node
{
    use NodeAttributes;
    public \PHPStan\PhpDocParser\Ast\Type\TypeNode $valueType;
    public ?\PHPStan\PhpDocParser\Ast\Type\TypeNode $keyType = null;
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\TypeNode $valueType, ?\PHPStan\PhpDocParser\Ast\Type\TypeNode $keyType)
    {
        $this->valueType = $valueType;
        $this->keyType = $keyType;
    }
    public function __toString() : string
    {
        if ($this->keyType !== null) {
            return sprintf('<%s, %s>', $this->keyType, $this->valueType);
        }
        return sprintf('<%s>', $this->valueType);
    }
}
