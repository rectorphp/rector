<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class SkipSelf
{
    public function getSelf(): self
    {
        return new self();
    }
}
