<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class DemoFile
{
    public function run()
    {
        throw new Exception("not implemented");
    }
    
   
}

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class DemoFile2 extends DemoFile {
    
    public function run() : Baba {
       return new Baba();        
    }
}

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class Baba {
}
?>
