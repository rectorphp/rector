<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException;

class CustomExceptionImported
{
    public function throwRootException()
    {
        throw new TheException('This is a root exeception');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException;

class CustomExceptionImported
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException
     */
    public function throwRootException()
    {
        throw new TheException('This is a root exeception');
    }
}

?>

