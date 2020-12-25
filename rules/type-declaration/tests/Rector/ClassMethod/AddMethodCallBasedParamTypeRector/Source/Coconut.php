<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source;

use Ramsey\Uuid\UuidInterface;

final class Coconut
{
    public function getId(): UuidInterface
    {
    }
}
