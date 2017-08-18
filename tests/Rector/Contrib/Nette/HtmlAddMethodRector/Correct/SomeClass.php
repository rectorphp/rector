<?php declare (strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\HtmlAddMethodRector\Correct;

use Nette\Utils\Html;

class SomeClass
{
    private function createHtml()
    {
        $html = new Html();
        $anotherHtml = $html;
        $anotherHtml->addHtml('someContent');
    }
}
