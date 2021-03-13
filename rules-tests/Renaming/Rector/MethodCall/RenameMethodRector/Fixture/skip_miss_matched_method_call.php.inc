<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

use Nette\Utils\Html;

class SkipMissMatchedMethodCall
{
    public function run($value = 'whatever')
    {
        $html = new Html();
        $html->{$value}();
    }
}
