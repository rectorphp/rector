<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class SomeClass
{
    public function run()
    {
        throw new InvalidException();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class SomeClass
{
    public function run(): never
    {
        throw new InvalidException();
    }
}

?>
