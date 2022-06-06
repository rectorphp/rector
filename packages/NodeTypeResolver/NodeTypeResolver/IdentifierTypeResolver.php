<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;

use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\StringType;
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
