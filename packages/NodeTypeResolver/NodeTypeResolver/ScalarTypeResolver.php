<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Scalar;
use RectorPrefix20220606\PhpParser\Node\Scalar\DNumber;
use RectorPrefix20220606\PhpParser\Node\Scalar\Encapsed;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Scalar\MagicConst;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantFloatType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantIntegerType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantStringType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220606\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
/**
 * @implements NodeTypeResolverInterface<Scalar>
 */
final class ScalarTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Scalar::class];
    }
    public function resolve(Node $node) : Type
    {
        if ($node instanceof DNumber) {
            return new ConstantFloatType((float) $node->value);
        }
        if ($node instanceof String_) {
            return new ConstantStringType((string) $node->value);
        }
        if ($node instanceof LNumber) {
            return new ConstantIntegerType((int) $node->value);
        }
        if ($node instanceof MagicConst) {
            return new ConstantStringType($node->getName());
        }
        if ($node instanceof Encapsed) {
            return new MixedType();
        }
        throw new NotImplementedYetException();
    }
}
