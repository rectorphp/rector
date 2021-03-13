<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponseInterface;

final class SkipAlreadySet
{
    public function action(): SomeResponseInterface
    {
        return new SomeResponse();
    }
}
