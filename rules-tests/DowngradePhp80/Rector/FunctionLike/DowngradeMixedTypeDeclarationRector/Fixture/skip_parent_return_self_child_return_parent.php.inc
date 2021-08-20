<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

abstract class A
{
    public abstract function camel(): self;
}

class B extends A
{
    public function camel(): parent
    {
        $x = clone $this;
        return $x;
    }
}
