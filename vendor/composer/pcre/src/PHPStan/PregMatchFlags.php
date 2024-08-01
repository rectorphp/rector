<?php

declare (strict_types=1);
namespace RectorPrefix202408\Composer\Pcre\PHPStan;

use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\Type;
use PhpParser\Node\Arg;
use PHPStan\Type\Php\RegexArrayShapeMatcher;
final class PregMatchFlags
{
    public static function getType(?Arg $flagsArg, Scope $scope) : ?Type
    {
        if ($flagsArg === null) {
            return new ConstantIntegerType(\PREG_UNMATCHED_AS_NULL | RegexArrayShapeMatcher::PREG_UNMATCHED_AS_NULL_ON_72_73);
        }
        $flagsType = $scope->getType($flagsArg->value);
        $constantScalars = $flagsType->getConstantScalarValues();
        if ($constantScalars === []) {
            return null;
        }
        $internalFlagsTypes = [];
        foreach ($flagsType->getConstantScalarValues() as $constantScalarValue) {
            if (!\is_int($constantScalarValue)) {
                return null;
            }
            $internalFlagsTypes[] = new ConstantIntegerType($constantScalarValue | \PREG_UNMATCHED_AS_NULL | RegexArrayShapeMatcher::PREG_UNMATCHED_AS_NULL_ON_72_73);
        }
        return TypeCombinator::union(...$internalFlagsTypes);
    }
}
