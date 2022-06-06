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
final class RamseyUuidInterfaceGuard implements \Rector\Naming\Contract\Guard\ConflictingNameGuardInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(\Rector\Naming\Contract\RenameValueObjectInterface $renameValueObject) : bool
    {
        return $this->nodeTypeResolver->isObjectType($renameValueObject->getProperty(), new \PHPStan\Type\ObjectType('Ramsey\\Uuid\\UuidInterface'));
    }
}
