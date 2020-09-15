<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use Nette\Utils\Strings;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Naming\RectorNamingInflector;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

/**
 * @deprecated
 * @todo merge with very similar logic in
 * @see VariableNaming
 * @see \Rector\Naming\Tests\Naming\PropertyNamingTest
 */
final class PropertyNaming
{
    /**
     * @var string[]
     */
    private const EXCLUDED_CLASSES = ['#Closure#', '#^Spl#', '#FileInfo#', '#^std#', '#Iterator#', '#SimpleXML#'];

    /**
     * @var string
     */
    private const INTERFACE = 'Interface';

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    /**
     * @var RectorNamingInflector
     */
    private $rectorNamingInflector;

    public function __construct(TypeUnwrapper $typeUnwrapper, RectorNamingInflector $rectorNamingInflector)
    {
        $this->typeUnwrapper = $typeUnwrapper;
        $this->rectorNamingInflector = $rectorNamingInflector;
    }

    public function getExpectedNameFromMethodName(string $methodName): ?ExpectedName
    {
        // @see https://regex101.com/r/hnU5pm/2/
        $matches = Strings::match($methodName, '#^get([A-Z].+)#');
        if ($matches === null) {
            return null;
        }

        $originalName = lcfirst($matches[1]);

        return new ExpectedName($originalName, $this->rectorNamingInflector->singularize($originalName));
    }

    public function getExpectedNameFromType(Type $type): ?ExpectedName
    {
        if ($type instanceof UnionType) {
            $type = $this->typeUnwrapper->unwrapNullableType($type);
        }

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

        // prolong too short generic names with one namespace up
        $originalName = $this->prolongIfTooShort($shortClassName, $className);
        return new ExpectedName($originalName, $this->rectorNamingInflector->singularize($originalName));
    }

    /**
     * @param ObjectType|string $objectType
     */
    public function fqnToVariableName($objectType): string
    {
        $className = $this->resolveClassName($objectType);

        $shortName = $this->fqnToShortName($className);
        $shortName = $this->removeInterfaceSuffixPrefix($className, $shortName);

        // prolong too short generic names with one namespace up
        return $this->prolongIfTooShort($shortName, $className);
    }

    /**
     * @source https://stackoverflow.com/a/2792045/1348344
     */
    public function underscoreToName(string $underscoreName): string
    {
        $pascalCaseName = str_replace('_', '', ucwords($underscoreName, '_'));

        return lcfirst($pascalCaseName);
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

    private function normalizeUpperCase(string $shortClassName): string
    {
        // turns $SOMEUppercase => $someUppercase
        for ($i = 0; $i <= strlen($shortClassName); ++$i) {
            if (ctype_upper($shortClassName[$i]) && $this->isNumberOrUpper($shortClassName[$i + 1])) {
                $shortClassName[$i] = strtolower($shortClassName[$i]);
            } else {
                break;
            }
        }

        return $shortClassName;
    }

    private function prolongIfTooShort(string $shortClassName, string $className): string
    {
        if (in_array($shortClassName, ['Factory', 'Repository'], true)) {
            /** @var string $namespaceAbove */
            $namespaceAbove = Strings::after($className, '\\', -2);
            /** @var string $namespaceAbove */
            $namespaceAbove = Strings::before($namespaceAbove, '\\');

            return lcfirst($namespaceAbove) . $shortClassName;
        }

        return lcfirst($shortClassName);
    }

    /**
     * @param ObjectType|string $objectType
     */
    private function resolveClassName($objectType): string
    {
        if ($objectType instanceof ObjectType) {
            return $objectType->getClassName();
        }

        return $objectType;
    }

    private function fqnToShortName(string $fqn): string
    {
        if (! Strings::contains($fqn, '\\')) {
            return $fqn;
        }

        /** @var string $lastNamePart */
        $lastNamePart = Strings::after($fqn, '\\', - 1);
        if (Strings::endsWith($lastNamePart, self::INTERFACE)) {
            return Strings::substring($lastNamePart, 0, - strlen(self::INTERFACE));
        }

        return $lastNamePart;
    }

    private function removeInterfaceSuffixPrefix(string $className, string $shortName): string
    {
        // remove interface prefix/suffix
        if (! interface_exists($className)) {
            return $shortName;
        }

        // starts with "I\W+"?
        if (Strings::match($shortName, '#^I[A-Z]#')) {
            return Strings::substring($shortName, 1);
        }

        if (Strings::match($shortName, '#Interface$#')) {
            return Strings::substring($shortName, -strlen(self::INTERFACE));
        }

        return $shortName;
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

    private function isNumberOrUpper(string $char): bool
    {
        if (ctype_upper($char)) {
            return true;
        }

        return ctype_digit($char);
    }
}
