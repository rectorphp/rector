<?php declare(strict_types=1);

namespace Rector\Naming;

final class PropertyNaming
{
    public function typeToName(string $serviceType): string
    {
        $serviceNameParts = explode('\\', $serviceType);
        $lastNamePart = array_pop($serviceNameParts);

        return lcfirst($lastNamePart);
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
        return in_array($type, [
            'string', 'bool', 'mixed', 'object', 'iterable', 'array',
        ], true);
    }
}
