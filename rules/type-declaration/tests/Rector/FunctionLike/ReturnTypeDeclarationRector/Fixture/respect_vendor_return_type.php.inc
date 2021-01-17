<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\RespectVendorReturnType;

class RespectVendorReturnType2 extends RespectVendorReturnType {
    public function run() {
       return true;       
    }
}
