<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Naming;

use Nette\Utils\Strings;
use PHPStan\Type\ObjectType;
use Rector\Naming\Naming\PropertyNaming;

final class ServiceNaming
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

    public function resolvePropertyNameFromServiceType(string $serviceType): string
    {
        if (Strings::contains($serviceType, '_') && ! Strings::contains($serviceType, '\\')) {
            return $this->propertyNaming->underscoreToName($serviceType);
        }

        return $this->propertyNaming->fqnToVariableName(new ObjectType($serviceType));
    }
}
