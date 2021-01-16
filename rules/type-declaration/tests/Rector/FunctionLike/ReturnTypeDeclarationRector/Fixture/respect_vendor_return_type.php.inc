<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class DemoFile
{
    /**
     * @return bool
     */
    public function run()
    {
        return false;
    }
}

class DemoFile2 extends DemoFile {
    public function run() {
       return true;       
    }
}
