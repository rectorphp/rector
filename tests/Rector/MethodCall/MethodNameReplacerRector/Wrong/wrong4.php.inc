<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Wrong;

use Nette\Utils\Html;

class SomeClass4
{
    private function createHtml()
    {
        $html = new Html();
        $html->add('someContent');
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Wrong;

use Nette\Utils\Html;

class SomeClass4
{
    private function createHtml()
    {
        $html = new Html();
        $html->addHtml('someContent');
    }
}

?>
