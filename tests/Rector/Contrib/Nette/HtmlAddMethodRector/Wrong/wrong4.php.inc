<?php declare (strict_types=1);

use Nette\Utils\Html;

class SomeClass
{
    private function createHtml()
    {
        $html = new Html();
        $html->add('someContent');
    }
}
