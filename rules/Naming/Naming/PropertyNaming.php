<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use RectorPrefix202610\Nette\Utils\Strings;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Exception\ShouldNotHappenException;
use Rector\Util\StringUtils;
/**
 * @see \Rector\Tests\Naming\Naming\PropertyNamingTest
 */
final class PropertyNaming
{
    /**
     * @var string
     */
    private const INTERFACE = 'Interface';
    /**
     * @see https://regex101.com/r/U78rUF/1
     * @var string
     */
    private const I_PREFIX_REGEX = '#^I[A-Z]#';
    /**
     * @param \PHPStan\Type\ThisType|\PHPStan\Type\ObjectType|\PhpParser\Node\Name|string $objectType
     */
    public function fqnToVariableName($objectType): string
    {
        if ($objectType instanceof Name) {
            $objectType = $objectType->toString();
        }
        if ($objectType instanceof ThisType) {
            $objectType = $objectType->getStaticObjectType();
        }
        $className = $this->resolveClassName($objectType);
        $shortClassName = strpos($className, '\\') !== \false ? (string) Strings::after($className, '\\', -1) : $className;
        $variableName = $this->removeInterfaceSuffixPrefix($shortClassName, 'interface');
        $variableName = $this->removeInterfaceSuffixPrefix($variableName, 'abstract');
        $variableName = $this->fqnToShortName($variableName);
        $variableName = str_replace('_', '', $variableName);
        // prolong too short generic names with one namespace up
        return $this->prolongIfTooShort($variableName, $className);
    }
    private function prolongIfTooShort(string $shortClassName, string $className): string
    {
        if (in_array($shortClassName, ['Factory', 'Repository'], \true) && substr_compare($className, 'Repository', -strlen('Repository')) !== 0 && substr_compare($className, 'Factory', -strlen('Factory')) !== 0) {
            $namespaceAbove = (string) Strings::after($className, '\\', -2);
            $namespaceAbove = (string) Strings::before($namespaceAbove, '\\');
            return lcfirst($namespaceAbove) . $shortClassName;
        }
        return lcfirst($shortClassName);
    }
    /**
     * @param \PHPStan\Type\ObjectType|string $objectType
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
        if (strpos($fqn, '\\') === \false) {
            return $fqn;
        }
        $lastNamePart = Strings::after($fqn, '\\', -1);
        if (!is_string($lastNamePart)) {
            throw new ShouldNotHappenException();
        }
        if (substr_compare($lastNamePart, self::INTERFACE, -strlen(self::INTERFACE)) === 0) {
            return Strings::substring($lastNamePart, 0, -strlen(self::INTERFACE));
        }
        return $lastNamePart;
    }
    private function removeInterfaceSuffixPrefix(string $className, string $category): string
    {
        // suffix
        $iSuffixMatch = Strings::match($className, '#' . $category . '$#i');
        if ($iSuffixMatch !== null) {
            return Strings::substring($className, 0, -strlen($category));
        }
        // prefix
        $iPrefixMatch = Strings::match($className, '#^' . $category . '#i');
        if ($iPrefixMatch !== null) {
            return Strings::substring($className, strlen($category));
        }
        // starts with "I\W+"?
        if (StringUtils::isMatch($className, self::I_PREFIX_REGEX)) {
            return Strings::substring($className, 1);
        }
        return $className;
    }
}
