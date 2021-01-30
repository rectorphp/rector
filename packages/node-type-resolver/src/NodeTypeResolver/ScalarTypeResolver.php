<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\Encapsed;
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

final class ScalarTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return class-string[]
     */
    public function getNodeClasses(): array
    {
        return [Scalar::class];
    }

    public function resolve(Node $node): Type
    {
        if ($node instanceof DNumber) {
            return new ConstantFloatType($node->value);
        }

        if ($node instanceof String_) {
            return new ConstantStringType($node->value);
        }

        if ($node instanceof LNumber) {
            return new ConstantIntegerType($node->value);
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
