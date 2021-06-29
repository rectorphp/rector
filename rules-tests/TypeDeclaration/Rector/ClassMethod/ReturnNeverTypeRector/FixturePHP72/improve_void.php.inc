<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

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

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

use Rector\Core\Exception\ShouldNotHappenException;

final class ImproveVoid
{
    /**
     * @return never
     */
    public function run(): void
    {
        throw new ShouldNotHappenException();
    }
}

?>
