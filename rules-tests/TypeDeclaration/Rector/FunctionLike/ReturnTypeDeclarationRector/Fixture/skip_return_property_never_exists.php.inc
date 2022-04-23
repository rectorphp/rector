<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class SkipReturnPropertyNotAutoloaded extends NotAutoloadedClass
{
    public function getName(): string
    {
        return $this->name;
    }
}