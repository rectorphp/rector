<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class SkipSelf
{
    public function getSelf(): self
    {
        return new self();
    }
}
