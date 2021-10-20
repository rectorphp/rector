<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use RectorPrefix20211020\Nette\Utils\Strings;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\Naming\RectorNamingInflector;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
/**
 * @deprecated
 * @todo merge with very similar logic in
 * @see VariableNaming
 * @see \Rector\Tests\Naming\Naming\PropertyNamingTest
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
     * @var string
     * @see https://regex101.com/r/U78rUF/1
     */
    private const I_PREFIX_REGEX = '#^I[A-Z]#';
    /**
     * @see https://regex101.com/r/hnU5pm/2/
     * @var string
     */
    private const GET_PREFIX_REGEX = '#^get(?<root_name>[A-Z].+)#';
    /**
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    /**
     * @var \Rector\Naming\RectorNamingInflector
     */
    private $rectorNamingInflector;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper $typeUnwrapper, \Rector\Naming\RectorNamingInflector $rectorNamingInflector, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->typeUnwrapper = $typeUnwrapper;
        $this->rectorNamingInflector = $rectorNamingInflector;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getExpectedNameFromMethodName(string $methodName) : ?\Rector\Naming\ValueObject\ExpectedName
    {
        $matches = \RectorPrefix20211020\Nette\Utils\Strings::match($methodName, self::GET_PREFIX_REGEX);
        if ($matches === null) {
            return null;
        }
        $originalName = \lcfirst($matches['root_name']);
        return new \Rector\Naming\ValueObject\ExpectedName($originalName, $this->rectorNamingInflector->singularize($originalName));
    }
    public function getExpectedNameFromType(\PHPStan\Type\Type $type) : ?\Rector\Naming\ValueObject\ExpectedName
    {
        $type = $this->typeUnwrapper->unwrapNullableType($type);
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType) {
            return null;
        }
        if ($type instanceof \PHPStan\Type\StaticType) {
            return null;
        }
        $className = $this->nodeTypeResolver->getFullyQualifiedClassName($type);
        // generic types are usually mix of parent type and specific type - various way to handle it
        if ($type instanceof \PHPStan\Type\Generic\GenericObjectType) {
            return null;
        }
        foreach (self::EXCLUDED_CLASSES as $excludedClass) {
            if (\RectorPrefix20211020\Nette\Utils\Strings::match($className, $excludedClass)) {
                return null;
            }
        }
        $shortClassName = $this->resolveShortClassName($className);
        $shortClassName = $this->removePrefixesAndSuffixes($shortClassName);
        // if all is upper-cased, it should be lower-cased
        if ($shortClassName === \strtoupper($shortClassName)) {
            $shortClassName = \strtolower($shortClassName);
        }
        // remove "_"
        $shortClassName = \RectorPrefix20211020\Nette\Utils\Strings::replace($shortClassName, '#_#', '');
        $shortClassName = $this->normalizeUpperCase($shortClassName);
        // prolong too short generic names with one namespace up
        $originalName = $this->prolongIfTooShort($shortClassName, $className);
        return new \Rector\Naming\ValueObject\ExpectedName($originalName, $this->rectorNamingInflector->singularize($originalName));
    }
    /**
     * @param \PHPStan\Type\ObjectType|string $objectType
     */
    public function fqnToVariableName($objectType) : string
    {
        $className = $this->resolveClassName($objectType);
        $shortName = $this->fqnToShortName($className);
        $shortName = $this->removeInterfaceSuffixPrefix($className, $shortName);
        // prolong too short generic names with one namespace up
        return $this->prolongIfTooShort($shortName, $className);
    }
    /**
     * @changelog https://stackoverflow.com/a/2792045/1348344
     */
    public function underscoreToName(string $underscoreName) : string
    {
        $uppercaseWords = \ucwords($underscoreName, '_');
        $pascalCaseName = \str_replace('_', '', $uppercaseWords);
        return \lcfirst($pascalCaseName);
    }
    private function resolveShortClassName(string $className) : string
    {
        if (\strpos($className, '\\') !== \false) {
            return (string) \RectorPrefix20211020\Nette\Utils\Strings::after($className, '\\', -1);
        }
        return $className;
    }
    private function removePrefixesAndSuffixes(string $shortClassName) : string
    {
        // is SomeInterface
        if (\substr_compare($shortClassName, self::INTERFACE, -\strlen(self::INTERFACE)) === 0) {
            $shortClassName = \RectorPrefix20211020\Nette\Utils\Strings::substring($shortClassName, 0, -\strlen(self::INTERFACE));
        }
        // is ISomeClass
        if ($this->isPrefixedInterface($shortClassName)) {
            $shortClassName = \RectorPrefix20211020\Nette\Utils\Strings::substring($shortClassName, 1);
        }
        // is AbstractClass
        if (\strncmp($shortClassName, 'Abstract', \strlen('Abstract')) === 0) {
            $shortClassName = \RectorPrefix20211020\Nette\Utils\Strings::substring($shortClassName, \strlen('Abstract'));
        }
        return $shortClassName;
    }
    private function normalizeUpperCase(string $shortClassName) : string
    {
        // turns $SOMEUppercase => $someUppercase
        for ($i = 0; $i <= \strlen($shortClassName); ++$i) {
            if (\ctype_upper($shortClassName[$i]) && $this->isNumberOrUpper($shortClassName[$i + 1])) {
                $shortClassName[$i] = \strtolower($shortClassName[$i]);
            } else {
                break;
            }
        }
        return $shortClassName;
    }
    private function prolongIfTooShort(string $shortClassName, string $className) : string
    {
        if (\in_array($shortClassName, ['Factory', 'Repository'], \true)) {
            $namespaceAbove = (string) \RectorPrefix20211020\Nette\Utils\Strings::after($className, '\\', -2);
            $namespaceAbove = (string) \RectorPrefix20211020\Nette\Utils\Strings::before($namespaceAbove, '\\');
            return \lcfirst($namespaceAbove) . $shortClassName;
        }
        return \lcfirst($shortClassName);
    }
    /**
     * @param \PHPStan\Type\ObjectType|string $objectType
     */
    private function resolveClassName($objectType) : string
    {
        if ($objectType instanceof \PHPStan\Type\ObjectType) {
            return $objectType->getClassName();
        }
        return $objectType;
    }
    private function fqnToShortName(string $fqn) : string
    {
        if (\strpos($fqn, '\\') === \false) {
            return $fqn;
        }
        /** @var string $lastNamePart */
        $lastNamePart = \RectorPrefix20211020\Nette\Utils\Strings::after($fqn, '\\', -1);
        if (\substr_compare($lastNamePart, self::INTERFACE, -\strlen(self::INTERFACE)) === 0) {
            return \RectorPrefix20211020\Nette\Utils\Strings::substring($lastNamePart, 0, -\strlen(self::INTERFACE));
        }
        return $lastNamePart;
    }
    private function removeInterfaceSuffixPrefix(string $className, string $shortName) : string
    {
        // remove interface prefix/suffix
        if (!$this->reflectionProvider->hasClass($className)) {
            return $shortName;
        }
        // starts with "I\W+"?
        if (\RectorPrefix20211020\Nette\Utils\Strings::match($shortName, self::I_PREFIX_REGEX)) {
            return \RectorPrefix20211020\Nette\Utils\Strings::substring($shortName, 1);
        }
        if (\substr_compare($shortName, self::INTERFACE, -\strlen(self::INTERFACE)) === 0) {
            return \RectorPrefix20211020\Nette\Utils\Strings::substring($shortName, -\strlen(self::INTERFACE));
        }
        return $shortName;
    }
    private function isPrefixedInterface(string $shortClassName) : bool
    {
        if (\strlen($shortClassName) <= 3) {
            return \false;
        }
        if (\strncmp($shortClassName, 'I', \strlen('I')) !== 0) {
            return \false;
        }
        if (!\ctype_upper($shortClassName[1])) {
            return \false;
        }
        return \ctype_lower($shortClassName[2]);
    }
    private function isNumberOrUpper(string $char) : bool
    {
        if (\ctype_upper($char)) {
            return \true;
        }
        return \ctype_digit($char);
    }
}
