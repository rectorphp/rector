<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\ConstExpr;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ConstExprArrayItemNode implements \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
{
    use NodeAttributes;
    public ?\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode $key = null;
    public \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode $value;
    public function __construct(?\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode $key, \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
    public function __toString() : string
    {
        if ($this->key !== null) {
            return sprintf('%s => %s', $this->key, $this->value);
        }
        return (string) $this->value;
    }
}
