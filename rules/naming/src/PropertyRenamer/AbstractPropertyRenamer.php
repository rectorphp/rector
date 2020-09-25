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
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;

abstract class AbstractPropertyRenamer
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

    public function rename(PropertyRename $propertyRename): ?Property
    {
        if ($this->areNamesDifferent($propertyRename)) {
            return null;
        }

        if ($this->propertyRenameGuard->shouldSkip($propertyRename, [
            $this->notPrivatePropertyGuard,
            $this->conflictingPropertyNameGuard,
            $this->ramseyUuidInterfaceGuard,
            $this->dateTimeAtNamingConventionGuard,
            $this->hasMagicGetSetGuard,
        ])) {
            return null;
        }

        $onlyPropertyProperty = $propertyRename->getPropertyProperty();
        $onlyPropertyProperty->name = new VarLikeIdentifier($propertyRename->getExpectedName());
        $this->renamePropertyFetchesInClass($propertyRename);

        return $propertyRename->getNode();
    }

    private function areNamesDifferent(PropertyRename $propertyRename): bool
    {
        return $propertyRename->getCurrentName() === $propertyRename->getExpectedName();
    }

    private function renamePropertyFetchesInClass(PropertyRename $propertyRename): void
    {
        // 1. replace property fetch rename in whole class
        $this->callableNodeTraverser->traverseNodesWithCallable(
            [$propertyRename->getClassLike()],
            function (Node $node) use ($propertyRename): ?Node {
                if ($this->nodeNameResolver->isLocalPropertyFetchNamed($node, $propertyRename->getCurrentName())) {
                    /** @var PropertyFetch $node */
                    $node->name = new Identifier($propertyRename->getExpectedName());
                    return $node;
                }

                if ($this->nodeNameResolver->isLocalStaticPropertyFetchNamed(
                    $node,
                    $propertyRename->getCurrentName()
                )) {
                    /** @var StaticPropertyFetch $node */
                    $node->name = new VarLikeIdentifier($propertyRename->getExpectedName());
                    return $node;
                }

                return null;
            }
        );
    }
}
