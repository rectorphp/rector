<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Wrong;

use Nette\Utils\Html;

final class SomeClass
{
    public function createHtml(): void
    {
        $html = new Html();
        $anotherHtml = $html;
        $anotherHtml->add('someContent');
    }
}
