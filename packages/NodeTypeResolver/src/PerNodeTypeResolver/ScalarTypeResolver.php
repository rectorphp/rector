<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use Rector\Exception\NotImplementedException;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class ScalarTypeResolver implements PerNodeTypeResolverInterface
{
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

        throw new NotImplementedException();
    }
}
