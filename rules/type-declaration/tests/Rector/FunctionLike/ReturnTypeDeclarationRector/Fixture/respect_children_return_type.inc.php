<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class RespectChildrenReturnType
{
    public function run()
    {
        throw new \Exception("not implemented");
    }
}

class DemoFile2 extends DemoFile {    
    public function run() : Baba {
       return new Baba();        
    }
}

class Baba {
}
?>
