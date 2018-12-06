<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Wrong;

class SomeClass
{
    private function createHtml()
    {
        $html = new \Nette\Utils\Html();
        $html->add('someContent');
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Wrong;

class SomeClass
{
    private function createHtml()
    {
        $html = new \Nette\Utils\Html();
        $html->addHtml('someContent');
    }
}

?>
