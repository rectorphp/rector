<?php

declare (strict_types=1);
namespace Rector\Naming;

use RectorPrefix20210509\Nette\Utils\Strings;
use Rector\Renaming\ValueObject\RenamedNamespace;
final class NamespaceMatcher
{
    /**
     * @param string[] $oldToNewNamespace
     */
    public function matchRenamedNamespace(string $name, array $oldToNewNamespace) : ?\Rector\Renaming\ValueObject\RenamedNamespace
    {
        \krsort($oldToNewNamespace);
        /** @var string $oldNamespace */
        foreach ($oldToNewNamespace as $oldNamespace => $newNamespace) {
            if (\RectorPrefix20210509\Nette\Utils\Strings::startsWith($name, $oldNamespace)) {
                return new \Rector\Renaming\ValueObject\RenamedNamespace($name, $oldNamespace, $newNamespace);
            }
        }
        return null;
    }
}
