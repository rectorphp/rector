<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class SkipRespectChildrenReturnTypeParentMixed
{
    public function run()
    {
        if (rand(0,1)) {
            return;
        }

        throw new \Exception("not implemented");
    }
}

final class RespectChildrenReturnTypeMixed extends SkipRespectChildrenReturnTypeParentMixed
{
    public function run(): string
    {
        return 'hey';
    }
}
