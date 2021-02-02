<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

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

class RespectChildrenReturnTypeMixed extends SkipRespectChildrenReturnTypeParentMixed {
    public function run() : Babax {
        if (rand(0,1)) {
            return new Babax();
        }

        throw new \Exception('test');
    }
}

class Babax {
}
?>
