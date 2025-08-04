<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Exception\NotImplementedYetException;
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
        if ($node instanceof Float_) {
            return new ConstantFloatType($node->value);
        }
        if ($node instanceof String_) {
            return new ConstantStringType($node->value);
        }
        if ($node instanceof Int_) {
            return new ConstantIntegerType($node->value);
        }
        if ($node instanceof MagicConst) {
            return new ConstantStringType($node->getName());
        }
        if ($node instanceof InterpolatedString) {
            return new StringType();
        }
        if ($node instanceof InterpolatedStringPart) {
            return new ConstantStringType($node->value);
        }
        throw new NotImplementedYetException();
    }
}
