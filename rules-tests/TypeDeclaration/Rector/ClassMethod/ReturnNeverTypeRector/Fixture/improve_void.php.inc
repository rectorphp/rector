<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Rector\Core\Exception\ShouldNotHappenException;

final class ImproveVoid
{
    public function run(): void
    {
        throw new ShouldNotHappenException();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Rector\Core\Exception\ShouldNotHappenException;

final class ImproveVoid
{
    public function run(): never
    {
        throw new ShouldNotHappenException();
    }
}

?>
