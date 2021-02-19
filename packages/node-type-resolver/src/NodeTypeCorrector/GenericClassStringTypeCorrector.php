<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;

final class GenericClassStringTypeCorrector
{
    /**
     * @var ClassLikeExistenceChecker
     */
    private $classLikeExistenceChecker;

    public function __construct(ClassLikeExistenceChecker $classLikeExistenceChecker)
    {
        $this->classLikeExistenceChecker = $classLikeExistenceChecker;
    }

    public function correct(Type $mainType): Type
    {
<<<<<<< HEAD
        // inspired from https://github.com/phpstan/phpstan-src/blob/94e3443b2d21404a821e05b901dd4b57fcbd4e7f/src/Type/Generic/TemplateTypeHelper.php#L18
        return TypeTraverser::map($mainType, function (Type $type, callable $traverse): Type {
=======
        // @todo extract own service
        // inspired from https://github.com/phpstan/phpstan-src/blob/94e3443b2d21404a821e05b901dd4b57fcbd4e7f/src/Type/Generic/TemplateTypeHelper.php#L18
        return TypeTraverser::map($mainType, function (Type $type, callable $traverse) {
>>>>>>> e4e29954a... [CodingStyle] Add array fixure iprovement
            if (! $type instanceof ConstantStringType) {
                return $traverse($type);
            }

            if (! $this->classLikeExistenceChecker->doesClassLikeExist($type->getValue())) {
                return $traverse($type);
            }

            return new GenericClassStringType(new ObjectType($type->getValue()));
        });
    }
}
