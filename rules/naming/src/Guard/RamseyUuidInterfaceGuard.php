<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use Ramsey\Uuid\UuidInterface;
use Rector\Naming\Contract\Guard\ConflictingGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class RamseyUuidInterfaceGuard implements ConflictingGuardInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @param PropertyRename $renameValueObject
     */
    public function check(RenameValueObjectInterface $renameValueObject): bool
    {
        return $this->nodeTypeResolver->isObjectType($renameValueObject->getProperty(), UuidInterface::class);
    }
}
