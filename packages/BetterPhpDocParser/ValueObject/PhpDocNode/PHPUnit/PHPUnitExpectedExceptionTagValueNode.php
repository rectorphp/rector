<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPUnit;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory\PHPUnitExpectedExceptionDocNodeFactory
 */
final class PHPUnitExpectedExceptionTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    /**
     * @var string
     */
    public const NAME = '@expectedException';

    /**
     * @var TypeNode
     */
    private $typeNode;

    public function __construct(TypeNode $typeNode)
    {
        $this->typeNode = $typeNode;
    }

    public function __toString(): string
    {
        return (string) $this->typeNode;
    }

    public function getTypeNode(): TypeNode
    {
        return $this->typeNode;
    }
}
