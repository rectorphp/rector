<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

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

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

final class SomeClass
{
    /**
     * @return never
     */
    public function run()
    {
        throw new InvalidException();
    }
}

?>
