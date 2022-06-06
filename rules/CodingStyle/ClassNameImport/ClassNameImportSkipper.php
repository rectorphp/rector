<?php

declare (strict_types=1);
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
     * @var ClassNameImportSkipVoterInterface[]
     * @readonly
     */
    private $classNameImportSkipVoters;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @param ClassNameImportSkipVoterInterface[] $classNameImportSkipVoters
     */
    public function __construct(array $classNameImportSkipVoters, \Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector)
    {
        $this->classNameImportSkipVoters = $classNameImportSkipVoters;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
    }
    public function shouldSkipNameForFullyQualifiedObjectType(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node $node, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        foreach ($this->classNameImportSkipVoters as $classNameImportSkipVoter) {
            if ($classNameImportSkipVoter->shouldSkip($file, $fullyQualifiedObjectType, $node)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Use_[] $existingUses
     */
    public function isShortNameInUseStatement(\PhpParser\Node\Name $name, array $existingUses) : bool
    {
        $longName = $name->toString();
        if (\strpos($longName, '\\') !== \false) {
            return \false;
        }
        return $this->isFoundInUse($name, $existingUses);
    }
    /**
     * @param Use_[] $uses
     */
    public function isAlreadyImported(\PhpParser\Node\Name $name, array $uses) : bool
    {
        $stringName = $name->toString();
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->name->toString() === $stringName) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @param Use_[] $uses
     */
    public function isFoundInUse(\PhpParser\Node\Name $name, array $uses) : bool
    {
        $stringName = $name->toString();
        $nameLastName = \strtolower($name->getLast());
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                $useUseLastName = \strtolower($useUse->name->getLast());
                if ($useUseLastName !== $nameLastName) {
                    continue;
                }
                if ($this->isJustRenamedClass($stringName, $useUse)) {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
    private function isJustRenamedClass(string $stringName, \PhpParser\Node\Stmt\UseUse $useUse) : bool
    {
        $useUseNameString = $useUse->name->toString();
        // is in renamed classes? skip it
        foreach ($this->renamedClassesDataCollector->getOldToNewClasses() as $oldClass => $newClass) {
            // is class being renamed in use imports?
            if ($stringName !== $newClass) {
                continue;
            }
            if ($useUseNameString !== $oldClass) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
