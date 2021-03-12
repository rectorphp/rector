<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class SkipSelf
{
    public function getSelf(): self
    {
        return new self();
    }
}
