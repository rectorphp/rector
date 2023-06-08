<?php

declare (strict_types=1);
namespace Rector\Naming\RenameGuard;

use PHPStan\Type\ObjectType;
use Rector\Naming\Guard\DateTimeAtNamingConventionGuard;
use Rector\Naming\Guard\HasMagicGetSetGuard;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class PropertyRenameGuard
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Guard\DateTimeAtNamingConventionGuard
     */
    private $dateTimeAtNamingConventionGuard;
    /**
     * @readonly
     * @var \Rector\Naming\Guard\HasMagicGetSetGuard
     */
    private $hasMagicGetSetGuard;
    public function __construct(NodeTypeResolver $nodeTypeResolver, DateTimeAtNamingConventionGuard $dateTimeAtNamingConventionGuard, HasMagicGetSetGuard $hasMagicGetSetGuard)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->dateTimeAtNamingConventionGuard = $dateTimeAtNamingConventionGuard;
        $this->hasMagicGetSetGuard = $hasMagicGetSetGuard;
    }
    public function shouldSkip(PropertyRename $propertyRename) : bool
    {
        if (!$propertyRename->isPrivateProperty()) {
            return \true;
        }
        if ($this->nodeTypeResolver->isObjectType($propertyRename->getProperty(), new ObjectType('Ramsey\\Uuid\\UuidInterface'))) {
            return \true;
        }
        if ($this->dateTimeAtNamingConventionGuard->isConflicting($propertyRename)) {
            return \true;
        }
        return $this->hasMagicGetSetGuard->isConflicting($propertyRename);
    }
}
