<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class ClassNameImportSkipper
{
    /**
     * @var ClassNameImportSkipVoterInterface[]
     */
    private $classNameImportSkipVoters = [];

    /**
     * @param ClassNameImportSkipVoterInterface[] $classNameImportSkipVoters
     */
    public function __construct(array $classNameImportSkipVoters)
    {
        $this->classNameImportSkipVoters = $classNameImportSkipVoters;
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
}
