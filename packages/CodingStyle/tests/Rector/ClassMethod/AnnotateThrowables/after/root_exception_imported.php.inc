<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use RuntimeException;

class RootExceptionImported
{
    public function throwRootException()
    {
        throw new RuntimeException('This is a root exeception');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use RuntimeException;

class RootExceptionImported
{
    /**
     * @throws RuntimeException
     */
    public function throwRootException()
    {
        throw new RuntimeException('This is a root exeception');
    }
}

?>

