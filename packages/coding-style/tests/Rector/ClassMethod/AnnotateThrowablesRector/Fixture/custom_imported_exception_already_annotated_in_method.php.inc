<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException;

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

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException;

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
