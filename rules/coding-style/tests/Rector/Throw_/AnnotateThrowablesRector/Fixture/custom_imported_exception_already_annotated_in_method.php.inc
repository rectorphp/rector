<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException;

class CustomImportedExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws TheException
     */
    public function throwException()
    {
        throw new TheException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException;

class CustomImportedExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws TheException
     */
    public function throwException()
    {
        throw new TheException();
    }
}

?>
