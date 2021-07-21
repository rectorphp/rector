<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\ValueObject\Application\File;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class ClassNameImportSkipper
{
    /**
     * @param ClassNameImportSkipVoterInterface[] $classNameImportSkipVoters
     */
    public function __construct(
        private array $classNameImportSkipVoters,
        private RenamedClassesDataCollector $renamedClassesDataCollector
    ) {
    }

    public function shouldSkipNameForFullyQualifiedObjectType(
        File $file,
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        foreach ($this->classNameImportSkipVoters as $classNameImportSkipVoter) {
            if ($classNameImportSkipVoter->shouldSkip($file, $fullyQualifiedObjectType, $node)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Use_[] $existingUses
     */
    public function isShortNameInUseStatement(Name $name, array $existingUses): bool
    {
        $longName = $name->toString();
        if (\str_contains($longName, '\\')) {
            return false;
        }

        return $this->isFoundInUse($name, $existingUses);
    }

    /**
     * @param Use_[] $uses
     */
    public function isAlreadyImported(Name $name, array $uses): bool
    {
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->name->toString() === $name->toString()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Use_[] $uses
     */
    public function isFoundInUse(Name $name, array $uses): bool
    {
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->name->getLast() !== $name->getLast()) {
                    continue;
                }

                if ($this->isJustRenamedClass($name, $useUse)) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    private function isJustRenamedClass(Name $name, UseUse $useUse): bool
    {
        // is in renamed classes? skip it
        foreach ($this->renamedClassesDataCollector->getOldToNewClasses() as $oldClass => $newClass) {
            // is class being renamed in use imports?
            if ($name->toString() !== $newClass) {
                continue;
            }

            if ($useUse->name->toString() !== $oldClass) {
                continue;
            }

            return true;
        }

        return false;
    }
}
