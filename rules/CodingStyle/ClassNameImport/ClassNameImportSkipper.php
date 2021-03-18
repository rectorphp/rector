<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\NodeTypeResolver\DataCollector\UsePerFileInfoDataCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClassNameImportSkipper
{
    /**
     * @var ClassNameImportSkipVoterInterface[]
     */
    private $classNameImportSkipVoters = [];

    /**
     * @var UsePerFileInfoDataCollector
     */
    private $usePerFileInfoDataCollector;

    /**
     * @param ClassNameImportSkipVoterInterface[] $classNameImportSkipVoters
     */
    public function __construct(
        UsePerFileInfoDataCollector $usePerFileInfoDataCollector,
        array $classNameImportSkipVoters
    ) {
        $this->classNameImportSkipVoters = $classNameImportSkipVoters;
        $this->usePerFileInfoDataCollector = $usePerFileInfoDataCollector;
    }

    public function shouldSkipNameForFullyQualifiedObjectType(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        foreach ($this->classNameImportSkipVoters as $classNameImportSkipVoter) {
            if ($classNameImportSkipVoter->shouldSkip($fullyQualifiedObjectType, $node)) {
                return true;
            }
        }

        return false;
    }

    public function isShortNameInUseStatement(Name $name): bool
    {
        $longName = $name->toString();
        if (Strings::contains($longName, '\\')) {
            return false;
        }

        return $this->isFoundInUse($name);
    }

    public function isFoundInUse(Name $name): bool
    {
        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $name->getAttribute(AttributeKey::FILE_INFO);
        $uses = $this->usePerFileInfoDataCollector->getUsesByFileInfo($fileInfo);

        foreach ($uses as $use) {
            $useUses = $use->uses;
            foreach ($useUses as $useUse) {
                if (! $useUse->name instanceof Name) {
                    continue;
                }
                if ($useUse->name->getLast() !== $name->getLast()) {
                    continue;
                }
                return true;
            }
        }

        return false;
    }
}
