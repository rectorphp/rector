<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Nette\Utils\Strings;

class SkipArrayAfterArrayType
{
    /**
     * @return string[]
     */
    public function getNewNamespaceForOldOne(string $namespace): array
    {
        /** @var string $oldNamespace */
        foreach ($this->getOldToNewNamespaces() as $oldNamespace => $newNamespace) {
            if (Strings::startsWith($namespace, $oldNamespace)) {
                return [$oldNamespace, $newNamespace];
            }
        }

        return [];
    }

    /**
     * @return string[]
     */
    private function getOldToNewNamespaces(): array
    {
        return ['one', 'two'];
    }

    /**
     * @return mixed[]
     */
    public function getAnEmptyArray(): array
    {
        return [];
    }
}
