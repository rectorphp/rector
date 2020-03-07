<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

class UseOfThisSameClassWithAssignment
{
    /**
     * @throws TheException
     */
    public function thisMethodThrowsAnException():string
    {
        throw new TheException('');
    }

    public function thisMethodUsesAThisThatThrowsAnException()
    {
        $assign = $this->thisMethodThrowsAnException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

class UseOfThisSameClassWithAssignment
{
    /**
     * @throws TheException
     */
    public function thisMethodThrowsAnException():string
    {
        throw new TheException('');
    }

    /**
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException
     */
    public function thisMethodUsesAThisThatThrowsAnException()
    {
        $assign = $this->thisMethodThrowsAnException();
    }
}

?>
