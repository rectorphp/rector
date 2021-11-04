<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use Rector\CodingStyle\ClassNameImport\ShortNameResolver;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\ValueObject\Application\File;

final class UseNameAliasToNameResolver
{
    public function __construct(
        private ClassNaming $classNaming,
        private ShortNameResolver $shortNameResolver
    ) {
    }

    /**
     * @return array<string, string[]>
     */
    public function resolve(File $file): array
    {
        $useNamesAliasToName = [];

        $shortNames = $this->shortNameResolver->resolveFromFile($file);
        foreach ($shortNames as $alias => $useImport) {
            if (! is_string($alias)) {
                continue;
            }

            $shortName = $this->classNaming->getShortName($useImport);
            if ($shortName === $alias) {
                continue;
            }

            $useNamesAliasToName[$shortName][] = $alias;
        }

        return $useNamesAliasToName;
    }
}
