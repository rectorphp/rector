<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class RenameMethodCallWithArrayKey
{
    private function createHtml()
    {
        $html = new \Nette\Utils\Html();
        $html->addToArray('someContent');
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class RenameMethodCallWithArrayKey
{
    private function createHtml()
    {
        $html = new \Nette\Utils\Html();
        $html->addToHtmlArray('someContent')['hey'];
    }
}

?>
