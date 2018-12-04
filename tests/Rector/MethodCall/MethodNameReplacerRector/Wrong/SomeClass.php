<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Wrong;

use Nette\Utils\Html;

final class SomeClass7
{
    public function createHtml(): void
    {
        $html = new Html();
        $anotherHtml = $html;
        $anotherHtml->add('someContent');
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Wrong;

use Nette\Utils\Html;

final class SomeClass7
{
    public function createHtml(): void
    {
        $html = new Html();
        $anotherHtml = $html;
        $anotherHtml->addHtml('someContent');
    }
}

?>
