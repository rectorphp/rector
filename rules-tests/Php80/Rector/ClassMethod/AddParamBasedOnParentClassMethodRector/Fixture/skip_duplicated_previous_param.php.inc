<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source\MultiParams;

/**
 * @bar, $bar will cause error Fatal error: Redefinition of parameter $bar
 *
 * @see https://3v4l.org/JaX5m
 */
class SkipDuplicatedPreviousParam extends MultiParams{
    public function execute($bar)
    {
        echo $bar;
    }
}
