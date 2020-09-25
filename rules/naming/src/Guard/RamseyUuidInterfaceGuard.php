<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use Ramsey\Uuid\UuidInterface;
use Rector\Naming\ValueObject\RenameValueObjectInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;

class RamseyUuidInterfaceGuard implements GuardInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function check(RenameValueObjectInterface $renameValueObject): bool
    {
        return $this->nodeTypeResolver->isObjectType($renameValueObject->getNode(), UuidInterface::class);
    }
}
