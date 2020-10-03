<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Naming\Guard\DateTimeAtNamingConventionGuard;
use Rector\Naming\Guard\GuardInterface;
use Rector\Naming\Guard\HasMagicGetSetGuard;
use Rector\Naming\Guard\NotPrivatePropertyGuard;
use Rector\Naming\Guard\RamseyUuidInterfaceGuard;
use Rector\Naming\RenameGuard\PropertyRenameGuard;
use Rector\Naming\RenameGuard\RenameGuardInterface;
use Rector\Naming\RenamerInterface;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObject\RenameValueObjectInterface;
use Rector\NodeNameResolver\NodeNameResolver;

abstract class AbstractPropertyRenamer implements RenamerInterface
{
    /**
     * @var RenameGuardInterface
     */
    protected $propertyRenameGuard;

    /**
     * @var GuardInterface
     */
    protected $conflictingPropertyNameGuard;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

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
        CallableNodeTraverser $callableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        NotPrivatePropertyGuard $notPrivatePropertyGuard,
        RamseyUuidInterfaceGuard $ramseyUuidInterfaceGuard,
        DateTimeAtNamingConventionGuard $dateTimeAtNamingConventionGuard,
        PropertyRenameGuard $propertyRenameGuard,
        HasMagicGetSetGuard $hasMagicGetSetGuard
    ): void {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->notPrivatePropertyGuard = $notPrivatePropertyGuard;
        $this->ramseyUuidInterfaceGuard = $ramseyUuidInterfaceGuard;
        $this->dateTimeAtNamingConventionGuard = $dateTimeAtNamingConventionGuard;
        $this->propertyRenameGuard = $propertyRenameGuard;
        $this->hasMagicGetSetGuard = $hasMagicGetSetGuard;
    }

    /**
     * @param PropertyRename $renameValueObject
     * @return Property|null
     */
    public function rename(RenameValueObjectInterface $renameValueObject): ?Node
    {
        if ($this->areNamesDifferent($renameValueObject)) {
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

    private function areNamesDifferent(RenameValueObjectInterface $renameValueObject): bool
    {
        return $renameValueObject->getCurrentName() === $renameValueObject->getExpectedName();
    }

    /**
     * @param PropertyRename $renameValueObject
     */
    private function renamePropertyFetchesInClass(RenameValueObjectInterface $renameValueObject): void
    {
        // 1. replace property fetch rename in whole class
        $this->callableNodeTraverser->traverseNodesWithCallable(
            [$renameValueObject->getClassLike()],
            function (Node $node) use ($renameValueObject): ?Node {
                if ($this->nodeNameResolver->isLocalPropertyFetchNamed($node, $renameValueObject->getCurrentName())) {
                    /** @var PropertyFetch $node */
                    $node->name = new Identifier($renameValueObject->getExpectedName());
                    return $node;
                }

                if ($this->nodeNameResolver->isLocalStaticPropertyFetchNamed(
                    $node,
                    $renameValueObject->getCurrentName()
                )) {
                    /** @var StaticPropertyFetch $node */
                    $node->name = new VarLikeIdentifier($renameValueObject->getExpectedName());
                    return $node;
                }

                return null;
            }
        );
    }
}
