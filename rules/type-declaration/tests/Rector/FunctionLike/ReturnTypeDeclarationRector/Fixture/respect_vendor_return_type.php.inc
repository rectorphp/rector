<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class RespectVendorReturnType
{
    /**
     * @return bool
     */
    public function run()
    {
        return false;
    }
}

class RespectVendorReturnType2 extends RespectVendorReturnType {
    public function run() {
       return true;       
    }
}
