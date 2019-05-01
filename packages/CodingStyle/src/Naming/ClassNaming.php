<?php declare(strict_types=1);

namespace Rector\CodingStyle\Naming;

use Nette\Utils\Strings;

final class ClassNaming
{
    public function getShortName(string $fullyQualifiedName): string
    {
        return Strings::after($fullyQualifiedName, '\\', -1) ?: $fullyQualifiedName;
    }
}
