<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Fixture;

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

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Fixture;

class SomeClass
{
    private function createHtml()
    {
        $html = new \Nette\Utils\Html();
        $html->addHtml('someContent');
    }
}

?>
