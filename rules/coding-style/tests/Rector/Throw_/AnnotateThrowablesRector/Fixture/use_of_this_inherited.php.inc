<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

class ThisA
{
    /**
     * @throws TheException
     */
    public function thisMethodThrowsAnException():string
    {
        throw new TheException('');
    }
}

class UseOfThisInherited extends ThisA
{
    public function thisMethodUsesAThisThatThrowsAnException()
    {
        $this->thisMethodThrowsAnException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

class ThisA
{
    /**
     * @throws TheException
     */
    public function thisMethodThrowsAnException():string
    {
        throw new TheException('');
    }
}

class UseOfThisInherited extends ThisA
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException
     */
    public function thisMethodUsesAThisThatThrowsAnException()
    {
        $this->thisMethodThrowsAnException();
    }
}

?>
