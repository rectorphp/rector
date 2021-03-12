<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use PhpParser\Node\Stmt\Return_;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Source\SomeInterface;

final class SkipByInterface implements SomeInterface
{
    public function run(Return_ $return)
    {
        $this->print($return);
    }

    public function print(\PhpParser\Node $return)
    {
    }
}
