<?php declare(strict_types=1);

namespace Rector\Naming;

final class PropertyNaming
{
    public function fqnToVariableName(string $fqn): string
    {
        return lcfirst($this->fqnToShortName($fqn));
    }

    public function fqnToShortName(string $fqn): string
    {
        $nameSpaceParts = explode('\\', $fqn);
        return end($nameSpaceParts);
    }

    /**
     * @source https://stackoverflow.com/a/2792045/1348344
     */
    public function underscoreToName(string $underscoreName): string
    {
        $camelCaseName = str_replace('_', '', ucwords($underscoreName, '_'));

        return lcfirst($camelCaseName);
    }

    public static function isPhpReservedType(string $type): bool
    {
        return in_array($type, ['string', 'bool', 'mixed', 'object', 'iterable', 'array'], true);
    }
}
