<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class ClassNameImportSkipper
{
    /**
     * @param ClassNameImportSkipVoterInterface[] $classNameImportSkipVoters
     */
    public function __construct(
        private array $classNameImportSkipVoters
    ) {
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
        /** @var Use_[] $uses */
        $uses = (array) $name->getAttribute(AttributeKey::USE_NODES);

        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->name->getLast() !== $name->getLast()) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }
}
