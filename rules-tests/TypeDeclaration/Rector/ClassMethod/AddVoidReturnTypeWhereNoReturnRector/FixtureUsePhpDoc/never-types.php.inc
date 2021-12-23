<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

final class SomeClass
{
    /**
     * @return never
     */
    public function neverReturn()
    {
        header('Location: example.com');
        exit();
    }

    /**
     * @return never-return
     */
    public function neverReturn()
    {
        header('Location: example.com');
        exit();
    }

    /**
     * @return never-returns
     */
    public function neverReturns()
    {
        header('Location: example.com');
        exit();
    }

    /**
     * @return no-return
     */
    public function noReturn()
    {
        header('Location: example.com');
        exit();
    }
}

?>