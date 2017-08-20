<?php declare (strict_types=1);

class SomeClass
{
    private function createHtml()
    {
        $html = new \Nette\Utils\Html();
        $html->add('someContent');
    }
}
