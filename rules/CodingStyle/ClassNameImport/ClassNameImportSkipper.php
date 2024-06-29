<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;
final class ClassNameImportSkipper
{
    /**
     * @var ClassNameImportSkipVoterInterface[]
     * @readonly
     */
    private $classNameImportSkipVoters;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @param ClassNameImportSkipVoterInterface[] $classNameImportSkipVoters
     */
    public function __construct(iterable $classNameImportSkipVoters, UseImportsResolver $useImportsResolver)
    {
        $this->classNameImportSkipVoters = $classNameImportSkipVoters;
        $this->useImportsResolver = $useImportsResolver;
    }
    public function shouldSkipNameForFullyQualifiedObjectType(File $file, Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        foreach ($this->classNameImportSkipVoters as $classNameImportSkipVoter) {
            if ($classNameImportSkipVoter->shouldSkip($file, $fullyQualifiedObjectType, $node)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param array<Use_|GroupUse> $uses
     */
    public function shouldSkipName(FullyQualified $fullyQualified, array $uses) : bool
    {
        if (\substr_count($fullyQualified->toCodeString(), '\\') <= 1) {
            return \false;
        }
        $stringName = $fullyQualified->toString();
        $lastUseName = $fullyQualified->getLast();
        $nameLastName = \strtolower($lastUseName);
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            $useName = $prefix . $stringName;
            foreach ($use->uses as $useUse) {
                $useUseLastName = \strtolower($useUse->name->getLast());
                if ($useUseLastName !== $nameLastName) {
                    continue;
                }
                if ($this->isConflictedShortNameInUse($useUse, $useName, $lastUseName, $stringName)) {
                    return \true;
                }
                return $prefix . $useUse->name->toString() !== $stringName;
            }
        }
        return \false;
    }
    private function isConflictedShortNameInUse(UseUse $useUse, string $useName, string $lastUseName, string $stringName) : bool
    {
        if (!$useUse->alias instanceof Identifier && $useName !== $stringName && $lastUseName === $stringName) {
            return \true;
        }
        return $useUse->alias instanceof Identifier && $useUse->alias->toString() === $stringName;
    }
}
