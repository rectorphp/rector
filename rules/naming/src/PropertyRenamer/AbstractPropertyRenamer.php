<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Naming\Contract\Guard\ConflictingGuardInterface;
use Rector\Naming\Contract\RenameGuard\RenameGuardInterface;
use Rector\Naming\Contract\RenamerInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\Guard\DateTimeAtNamingConventionGuard;
use Rector\Naming\Guard\HasMagicGetSetGuard;
use Rector\Naming\Guard\NotPrivatePropertyGuard;
use Rector\Naming\Guard\RamseyUuidInterfaceGuard;
use Rector\Naming\RenameGuard\PropertyRenameGuard;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

abstract class AbstractPropertyRenamer implements RenamerInterface
{
    /**
     * @var RenameGuardInterface
     */
    protected $propertyRenameGuard;

    /**
     * @var ConflictingGuardInterface
     */
    protected $conflictingPropertyNameGuard;

    /**
     * @var PropertyFetchRenamer
     */
    protected $propertyFetchRenamer;

    /**
     * @var NotPrivatePropertyGuard
     */
    private $notPrivatePropertyGuard;

    /**
     * @var RamseyUuidInterfaceGuard
     */
    private $ramseyUuidInterfaceGuard;

    /**
     * @var DateTimeAtNamingConventionGuard
     */
    private $dateTimeAtNamingConventionGuard;

    /**
     * @var HasMagicGetSetGuard
     */
    private $hasMagicGetSetGuard;

    /**
     * @required
     */
    public function autowireAbstractPropertyRenamer(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        NotPrivatePropertyGuard $notPrivatePropertyGuard,
        RamseyUuidInterfaceGuard $ramseyUuidInterfaceGuard,
        DateTimeAtNamingConventionGuard $dateTimeAtNamingConventionGuard,
        PropertyRenameGuard $propertyRenameGuard,
        HasMagicGetSetGuard $hasMagicGetSetGuard,
        PropertyFetchRenamer $propertyFetchRenamer
    ): void {
        $this->notPrivatePropertyGuard = $notPrivatePropertyGuard;
        $this->ramseyUuidInterfaceGuard = $ramseyUuidInterfaceGuard;
        $this->dateTimeAtNamingConventionGuard = $dateTimeAtNamingConventionGuard;
        $this->propertyRenameGuard = $propertyRenameGuard;
        $this->hasMagicGetSetGuard = $hasMagicGetSetGuard;
        $this->propertyFetchRenamer = $propertyFetchRenamer;
    }

    /**
     * @param PropertyRename $renameValueObject
     * @return Property|null
     */
    public function rename(RenameValueObjectInterface $renameValueObject): ?Node
    {
        if (! $this->areNamesDifferent($renameValueObject)) {
            return null;
        }

        if ($this->propertyRenameGuard->shouldSkip($renameValueObject, [
            $this->notPrivatePropertyGuard,
            $this->conflictingPropertyNameGuard,
            $this->ramseyUuidInterfaceGuard,
            $this->dateTimeAtNamingConventionGuard,
            $this->hasMagicGetSetGuard,
        ])) {
            return null;
        }

        $onlyPropertyProperty = $renameValueObject->getPropertyProperty();
        $onlyPropertyProperty->name = new VarLikeIdentifier($renameValueObject->getExpectedName());
        $this->renamePropertyFetchesInClass($renameValueObject);

        return $renameValueObject->getProperty();
    }

    protected function renamePropertyFetchesInClass(PropertyRename $propertyRename): void
    {
        $this->propertyFetchRenamer->renamePropertyFetchesInClass(
            $propertyRename->getClassLike(),
            $propertyRename->getCurrentName(),
            $propertyRename->getExpectedName()
        );
    }

    private function areNamesDifferent(PropertyRename $propertyRename): bool
    {
        return $propertyRename->getCurrentName() !== $propertyRename->getExpectedName();
    }
}
