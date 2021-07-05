<?php

declare (strict_types=1);
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
final class ScalarTypeResolver implements \Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [\PhpParser\Node\Scalar::class];
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : \PHPStan\Type\Type
    {
        if ($node instanceof \PhpParser\Node\Scalar\DNumber) {
            return new \PHPStan\Type\Constant\ConstantFloatType($node->value);
        }
        if ($node instanceof \PhpParser\Node\Scalar\String_) {
            return new \PHPStan\Type\Constant\ConstantStringType($node->value);
        }
        if ($node instanceof \PhpParser\Node\Scalar\LNumber) {
            return new \PHPStan\Type\Constant\ConstantIntegerType($node->value);
        }
        if ($node instanceof \PhpParser\Node\Scalar\MagicConst) {
            return new \PHPStan\Type\Constant\ConstantStringType($node->getName());
        }
        if ($node instanceof \PhpParser\Node\Scalar\Encapsed) {
            return new \PHPStan\Type\MixedType();
        }
        throw new \Rector\Core\Exception\NotImplementedYetException();
    }
}
