<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use PhpParser\Node\Expr\ClassConstFetch;

final class SkipDoubleMethodCall
{
    public function run()
    {
        $magicGet = $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_GET');
        $magicSet = $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_SET');
    }

    protected function createClassConstFetch(string $class, string $constant): ClassConstFetch
    {
        return new ClassConstFetch($class, $constant);
    }
}
?>
