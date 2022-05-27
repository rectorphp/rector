<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node\Identifier;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
final class IdentifierTypeResolver
{
    /**
     * @return \PHPStan\Type\StringType|\PHPStan\Type\BooleanType|\PHPStan\Type\IntegerType|\PHPStan\Type\FloatType|\PHPStan\Type\MixedType
     */
    public function resolve(Identifier $identifier)
    {
        if ($identifier->toLowerString() === 'string') {
            return new StringType();
        }
        if ($identifier->toLowerString() === 'bool') {
            return new BooleanType();
        }
        if ($identifier->toLowerString() === 'int') {
            return new IntegerType();
        }
        if ($identifier->toLowerString() === 'float') {
            return new FloatType();
        }
        return new MixedType();
    }
}
