<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ObjectShapeItemNode implements Node
{
    use NodeAttributes;
    /** @var ConstExprStringNode|IdentifierTypeNode */
    public $keyName;
    public bool $optional;
    public \PHPStan\PhpDocParser\Ast\Type\TypeNode $valueType;
    /**
     * @param ConstExprStringNode|IdentifierTypeNode $keyName
     */
    public function __construct($keyName, bool $optional, \PHPStan\PhpDocParser\Ast\Type\TypeNode $valueType)
    {
        $this->keyName = $keyName;
        $this->optional = $optional;
        $this->valueType = $valueType;
    }
    public function __toString() : string
    {
        if ($this->keyName !== null) {
            return sprintf('%s%s: %s', (string) $this->keyName, $this->optional ? '?' : '', (string) $this->valueType);
        }
        return (string) $this->valueType;
    }
}
