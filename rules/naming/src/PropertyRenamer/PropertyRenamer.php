<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Naming\RenameGuard\PropertyRenameGuard;
use Rector\Naming\ValueObject\PropertyRename;

final class PropertyRenamer
{
    /**
     * @var PropertyFetchRenamer
     */
    private $propertyFetchRenamer;

    /**
     * @var PropertyRenameGuard
     */
    private $propertyRenameGuard;

    public function __construct(
        PropertyRenameGuard $propertyRenameGuard,
        PropertyFetchRenamer $propertyFetchRenamer
    ) {
        $this->propertyRenameGuard = $propertyRenameGuard;
        $this->propertyFetchRenamer = $propertyFetchRenamer;
    }

    public function rename(PropertyRename $propertyRename): ?Property
    {
        if ($propertyRename->isAlreadyExpectedName()) {
            return null;
        }

        if ($this->propertyRenameGuard->shouldSkip($propertyRename)) {
            return null;
        }

        $onlyPropertyProperty = $propertyRename->getPropertyProperty();
        $onlyPropertyProperty->name = new VarLikeIdentifier($propertyRename->getExpectedName());
        $this->renamePropertyFetchesInClass($propertyRename);

        return $propertyRename->getProperty();
    }

    private function renamePropertyFetchesInClass(PropertyRename $propertyRename): void
    {
        $this->propertyFetchRenamer->renamePropertyFetchesInClass(
            $propertyRename->getClassLike(),
            $propertyRename->getCurrentName(),
            $propertyRename->getExpectedName()
        );
    }
}
