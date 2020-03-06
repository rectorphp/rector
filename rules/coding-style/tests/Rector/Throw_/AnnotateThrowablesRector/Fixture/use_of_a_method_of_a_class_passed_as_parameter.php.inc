<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

class A
{
    /**
     * @throws TheException
     */
    public function thisMethodThrowsAnException():string
    {
        throw new TheException('');
    }
}

class UseAClassWithAMethodThatMayThrowAnException
{
    /**
     * @param A $theClass
     */
    public function thisMethodUsesTheClassWithTheMethodThatTrhowsAnException(A $theClass)
    {
        $aVariable = $theClass->thisMethodThrowsAnException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

class A
{
    /**
     * @throws TheException
     */
    public function thisMethodThrowsAnException():string
    {
        throw new TheException('');
    }
}

class UseAClassWithAMethodThatMayThrowAnException
{
    /**
     * @param A $theClass
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException
     */
    public function thisMethodUsesTheClassWithTheMethodThatTrhowsAnException(A $theClass)
    {
        $aVariable = $theClass->thisMethodThrowsAnException();
    }
}

?>
