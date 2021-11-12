<?php
namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

class X {
    protected function configBoot()
    {
        if (isset($helper_files)) {
            throw new Exception('error');
        }
        if (isset($builtin_files)) {
            throw new Exception('error');
        }
    }
}
