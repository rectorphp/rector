<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Node;

use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\ClassNameImport\ShortNameResolver;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\ValueObject\Application\File;
final class UseNameAliasToNameResolver
{
    /**
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @var \Rector\CodingStyle\ClassNameImport\ShortNameResolver
     */
    private $shortNameResolver;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming, \Rector\CodingStyle\ClassNameImport\ShortNameResolver $shortNameResolver)
    {
        $this->classNaming = $classNaming;
        $this->shortNameResolver = $shortNameResolver;
    }
    /**
     * @return array<string, string[]>
     */
    public function resolve(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node\Stmt\Use_ $use) : array
    {
        $useNamesAliasToName = [];
        $shortNames = $this->shortNameResolver->resolveForNode($file);
        foreach ($shortNames as $alias => $useImport) {
            if (!\is_string($alias)) {
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
