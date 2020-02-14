<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

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
