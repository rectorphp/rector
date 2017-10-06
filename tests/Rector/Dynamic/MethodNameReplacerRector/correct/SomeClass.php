<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Utils\HtmlAddMethodRector\Wrong;

use Nette\Utils\Html;

final class SomeClass
{
    public function createHtml(): void
    {
        $html = new Html;
        $anotherHtml = $html;
        $anotherHtml->addHtml('someContent');
    }
}
