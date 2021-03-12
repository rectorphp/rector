<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use PhpParser\Node\Stmt\Return_;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeFromCallersRector\Source\SomeInterface;

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
