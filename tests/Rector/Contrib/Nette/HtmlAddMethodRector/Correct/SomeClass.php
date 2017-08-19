<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\HtmlAddMethodRector\Correct;

use Nette\Utils\Html;

final class SomeClass
{
    private function createHtml(): void
    {
        $html = new Html;
        $anotherHtml = $html;
        $anotherHtml->addHtml('someContent');
    }
}
