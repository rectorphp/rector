<?php declare(strict_types=1);

namespace Rector\ContributorTools;

use Nette\Utils\Strings;

final class RectorMetadataResolver
{
    public function resolvePackageFromRectorClass(string $rectorClass): string
    {
        $rectorClassParts = explode('\\', $rectorClass);

        // basic Rectors
        if (Strings::startsWith($rectorClass, 'Rector\Rector\\')) {
            return 'Core';
        }

        // Rector/<PackageGroup>/Rector/SomeRector
        return $rectorClassParts[1];
    }
}
