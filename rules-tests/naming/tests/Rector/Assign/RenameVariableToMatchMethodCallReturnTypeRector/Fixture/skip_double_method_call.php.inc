<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use PhpParser\Node\Expr\ClassConstFetch;

final class SkipDoubleMethodCall
{
    public function run()
    {
        $magicGet = $this->createClassConstFetch('SomeClass', 'MAGIC_GET');
        $magicSet = $this->createClassConstFetch('SomeClass', 'MAGIC_SET');
    }

    protected function createClassConstFetch(string $class, string $constant): ClassConstFetch
    {
        return new ClassConstFetch($class, $constant);
    }
}
?>
