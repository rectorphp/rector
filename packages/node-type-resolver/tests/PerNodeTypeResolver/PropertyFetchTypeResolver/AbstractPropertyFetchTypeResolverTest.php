<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver;

use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

abstract class AbstractPropertyFetchTypeResolverTest extends AbstractNodeTypeResolverTest
{
    protected function getStringFromType(Type $type): string
    {
        return $type->describe(VerbosityLevel::precise());
    }
}
