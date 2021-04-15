<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\TypeAnalyzer;

use DateTimeInterface;
use Nette\Utils\Strings;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class AllowedAutoloadedTypeAnalyzer
{
    /**
     * @see https://regex101.com/r/BBm9bf/1
     * @var string
     */
    private const AUTOLOADED_CLASS_PREFIX_REGEX = '#^(PhpParser|PHPStan|Rector|Reflection|Symfony\\\\Component\\\\Console)#';

    /**
     * @var array<class-string>
     */
    private const ALLOWED_CLASSES = [DateTimeInterface::class, 'Symplify\SmartFileSystem\SmartFileInfo'];

    public function isAllowedType(Type $type): bool
    {
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (! $this->isAllowedType($unionedType)) {
                    return false;
                }
            }

            return true;
        }

        if ($type instanceof ConstantStringType) {
            return $this->isAllowedClassString($type->getValue());
        }

        if ($type instanceof ObjectType) {
            return $this->isAllowedClassString($type->getClassName());
        }

        if ($type instanceof GenericClassStringType) {
            return $this->isAllowedType($type->getGenericType());
        }

        return false;
    }

    private function isAllowedClassString(string $value): bool
    {
        // autoloaded allowed type
        if (Strings::match($value, self::AUTOLOADED_CLASS_PREFIX_REGEX)) {
            return true;
        }

        foreach (self::ALLOWED_CLASSES as $allowedClass) {
            if ($value === $allowedClass) {
                return true;
            }
        }

        return false;
    }
}
