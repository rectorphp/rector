<?php declare(strict_types=1);

namespace Rector\CodingStyle\Naming;

use Nette\Utils\Strings;

final class ClassNaming
{
    public function getShortName(string $fullyQualifiedName): string
    {
        $fullyQualifiedName = trim($fullyQualifiedName, '\\');

        return Strings::after($fullyQualifiedName, '\\', -1) ?: $fullyQualifiedName;
    }

    public function getNamespace(string $fullyQualifiedName): ?string
    {
        $fullyQualifiedName = trim($fullyQualifiedName, '\\');

        return Strings::before($fullyQualifiedName, '\\', -1) ?: null;
    }
}
