<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp80;

use PHPStan\Type\Type;

final class SkipPHPStanTypeConstruct
{
    public function __construct(
        private Type $returnType
    ) {
    }

    public function getReturnType(): Type
    {
        return $this->returnType;
    }
}
