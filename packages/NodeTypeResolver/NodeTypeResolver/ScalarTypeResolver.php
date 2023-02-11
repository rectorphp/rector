<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
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
        if ($node instanceof EncapsedStringPart) {
            return new ConstantStringType($node->value);
        }
        throw new NotImplementedYetException();
    }
}
