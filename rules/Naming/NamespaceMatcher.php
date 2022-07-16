<?php

declare (strict_types=1);
namespace Rector\Naming;

use Rector\Renaming\ValueObject\RenamedNamespace;
final class NamespaceMatcher
{
    /**
     * @param string[] $oldToNewNamespace
     */
    public function matchRenamedNamespace(string $name, array $oldToNewNamespace) : ?RenamedNamespace
    {
        \krsort($oldToNewNamespace);
        /** @var string $oldNamespace */
        foreach ($oldToNewNamespace as $oldNamespace => $newNamespace) {
            if ($name === $oldNamespace) {
                return new RenamedNamespace($name, $oldNamespace, $newNamespace);
            }
            if (\strncmp($name, $oldNamespace . '\\', \strlen($oldNamespace . '\\')) === 0) {
                return new RenamedNamespace($name, $oldNamespace, $newNamespace);
            }
        }
        return null;
    }
}
