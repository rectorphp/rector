<?php

declare(strict_types=1);

namespace Rector\Core\Naming;

use Nette\Utils\Strings;
use Rector\Core\ValueObject\RenamedNamespaceValueObject;

final class NamespaceMatcher
{
    /**
     * @param string[] $oldToNewNamespace
     */
    public function matchRenamedNamespace(string $name, array $oldToNewNamespace): ?RenamedNamespaceValueObject
    {
        krsort($oldToNewNamespace);

        /** @var string $oldNamespace */
        foreach ($oldToNewNamespace as $oldNamespace => $newNamespace) {
            if (Strings::startsWith($name, $oldNamespace)) {
                return new RenamedNamespaceValueObject($name, $oldNamespace, $newNamespace);
            }
        }

        return null;
    }
}
