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
}
