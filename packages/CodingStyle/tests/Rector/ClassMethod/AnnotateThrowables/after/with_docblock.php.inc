<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class WithDocblock
{
    /**
     * @param string $hello
     */
    public function throwRootException(string $hello)
    {
        throw new \RuntimeException('This is a root exeception');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class WithDocblock
{
    /**
     * @param string $hello
     * @throws \RuntimeException
     */
    public function throwRootException(string $hello)
    {
        throw new \RuntimeException('This is a root exeception');
    }
}

?>

