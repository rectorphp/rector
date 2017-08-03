<?php declare(strict_types=1);

namespace Rector\Builder\Naming;

final class NameResolver
{
    public function resolvePropertyNameFromType(string $serviceType): string
    {
        $serviceNameParts = explode('\\', $serviceType);
        $lastNamePart = array_pop($serviceNameParts);

        return lcfirst($lastNamePart);
    }
}
