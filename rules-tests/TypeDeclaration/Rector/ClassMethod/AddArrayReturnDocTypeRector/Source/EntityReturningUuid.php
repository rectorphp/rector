<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EntityReturningUuid
{
    public function getId(): UuidInterface
    {
        return Uuid::uuid4();
    }
}
