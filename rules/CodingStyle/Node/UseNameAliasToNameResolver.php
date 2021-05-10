<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use PhpParser\Node\Stmt\Use_;
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
    public function resolve(File $file, Use_ $use): array
    {
        $useNamesAliasToName = [];

        $shortNames = $this->shortNameResolver->resolveForNode($file);
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
