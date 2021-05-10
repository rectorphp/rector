<?php

declare (strict_types=1);
namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Naming\RenameGuard\PropertyRenameGuard;
use Rector\Naming\ValueObject\PropertyRename;
final class PropertyRenamer
{
    /**
     * @var \Rector\Naming\RenameGuard\PropertyRenameGuard
     */
    private $propertyRenameGuard;
    /**
     * @var \Rector\Naming\PropertyRenamer\PropertyFetchRenamer
     */
    private $propertyFetchRenamer;
    public function __construct(\Rector\Naming\RenameGuard\PropertyRenameGuard $propertyRenameGuard, \Rector\Naming\PropertyRenamer\PropertyFetchRenamer $propertyFetchRenamer)
    {
        $this->propertyRenameGuard = $propertyRenameGuard;
        $this->propertyFetchRenamer = $propertyFetchRenamer;
    }
    public function rename(\Rector\Naming\ValueObject\PropertyRename $propertyRename) : ?\PhpParser\Node\Stmt\Property
    {
        if ($propertyRename->isAlreadyExpectedName()) {
            return null;
        }
        if ($this->propertyRenameGuard->shouldSkip($propertyRename)) {
            return null;
        }
        $onlyPropertyProperty = $propertyRename->getPropertyProperty();
        $onlyPropertyProperty->name = new \PhpParser\Node\VarLikeIdentifier($propertyRename->getExpectedName());
        $this->renamePropertyFetchesInClass($propertyRename);
        return $propertyRename->getProperty();
    }
    private function renamePropertyFetchesInClass(\Rector\Naming\ValueObject\PropertyRename $propertyRename) : void
    {
        $this->propertyFetchRenamer->renamePropertyFetchesInClass($propertyRename->getClassLike(), $propertyRename->getCurrentName(), $propertyRename->getExpectedName());
    }
}
