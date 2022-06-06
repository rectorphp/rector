<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Guard;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use RectorPrefix20220606\Rector\Naming\Contract\RenameValueObjectInterface;
use RectorPrefix20220606\Rector\Naming\ValueObject\PropertyRename;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
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
