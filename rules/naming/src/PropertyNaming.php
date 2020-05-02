<?php

declare(strict_types=1);

namespace Rector\Naming;

use Nette\Utils\Strings;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;

final class PropertyNaming
{
    /**
     * @var string[]
     */
    private const EXCLUDED_CLASSES = [
        '#Closure#',
        '#^Spl#',
        '#FileInfo#',
        '#^std#',
        '#Iterator#',
        '#SimpleXML#',
        // anything that ends with underscore
        '#_$#',
    ];

    public function getExpectedNameFromType(Type $type): ?string
    {
        if (! $type instanceof TypeWithClassName) {
            return null;
        }

        if ($type instanceof SelfObjectType) {
            return null;
        }

        if ($type instanceof StaticType) {
            return null;
        }

        $className = $this->getClassName($type);

        foreach (self::EXCLUDED_CLASSES as $excludedClass) {
            if (Strings::match($className, $excludedClass)) {
                return null;
            }
        }

        $shortClassName = $this->resolveShortClassName($className);
        $shortClassName = $this->removePrefixesAndSuffixes($shortClassName);

        // if all is upper-cased, it should be lower-cased
        if ($shortClassName === strtoupper($shortClassName)) {
            $shortClassName = strtolower($shortClassName);
        }

        // remove "_"
        $shortClassName = Strings::replace($shortClassName, '#_#', '');

        $shortClassName = $this->normalizeUpperCase($shortClassName);

        return lcfirst($shortClassName);
    }

    private function removePrefixesAndSuffixes(string $shortClassName): string
    {
        // is SomeInterface
        if (Strings::endsWith($shortClassName, 'Interface')) {
            $shortClassName = Strings::substring($shortClassName, 0, -strlen('Interface'));
        }

        // is ISomeClass
        if ($this->isPrefixedInterface($shortClassName)) {
            $shortClassName = Strings::substring($shortClassName, 1);
        }

        // is AbstractClass
        if (Strings::startsWith($shortClassName, 'Abstract')) {
            $shortClassName = Strings::substring($shortClassName, strlen('Abstract'));
        }

        return $shortClassName;
    }

    private function isPrefixedInterface(string $shortClassName): bool
    {
        if (strlen($shortClassName) <= 3) {
            return false;
        }

        if (! Strings::startsWith($shortClassName, 'I')) {
            return false;
        }

        return ctype_upper($shortClassName[1]) && ctype_lower($shortClassName[2]);
    }

    private function getClassName(TypeWithClassName $typeWithClassName): string
    {
        if ($typeWithClassName instanceof ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }

        return $typeWithClassName->getClassName();
    }

    private function resolveShortClassName(string $className): string
    {
        if (Strings::contains($className, '\\')) {
            return Strings::after($className, '\\', -1);
        }

        return $className;
    }

    private function normalizeUpperCase(string $shortClassName): string
    {
        // turns $SOMEUppercase => $someUppercase
        for ($i = 0; $i <= strlen($shortClassName); ++$i) {
            if (ctype_upper($shortClassName[$i]) && ctype_upper($shortClassName[$i + 1])) {
                $shortClassName[$i] = strtolower($shortClassName[$i]);
            } else {
                break;
            }
        }
        return $shortClassName;
    }
}
