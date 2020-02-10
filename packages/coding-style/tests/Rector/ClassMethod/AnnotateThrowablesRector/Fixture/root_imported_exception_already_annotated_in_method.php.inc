<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use \RuntimeException;

class RootImportedExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws RuntimeException
     */
    public function throwException()
    {
        throw new RuntimeException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use \RuntimeException;

class RootImportedExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws RuntimeException
     */
    public function throwException()
    {
        throw new RuntimeException();
    }
}

?>
