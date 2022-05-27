<?php

declare (strict_types=1);
namespace Rector\Naming\Guard;

use PHPStan\Type\ObjectType;
use Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeTypeResolver\NodeTypeResolver;
/**
 * @implements ConflictingNameGuardInterface<PropertyRename>
 */
final class RamseyUuidInterfaceGuard implements ConflictingNameGuardInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(RenameValueObjectInterface $renameValueObject) : bool
    {
        return $this->nodeTypeResolver->isObjectType($renameValueObject->getProperty(), new ObjectType('Ramsey\\Uuid\\UuidInterface'));
    }
}
