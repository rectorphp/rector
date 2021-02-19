<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponseInterface;

final class SkipAlreadySet
{
    public function action(): SomeResponseInterface
    {
        return new SomeResponse();
    }
}
