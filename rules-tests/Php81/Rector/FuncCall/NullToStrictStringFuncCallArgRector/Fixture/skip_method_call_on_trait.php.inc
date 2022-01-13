<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

trait SkipMethodCallOnTrait
{
    public function run()
    {
        $obj = $this->foo->getBar();
        preg_split('#a#', $obj->getValue());
    }
}
