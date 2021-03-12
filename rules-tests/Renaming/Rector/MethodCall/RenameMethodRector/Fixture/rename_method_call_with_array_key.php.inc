<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

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

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

class RenameMethodCallWithArrayKey
{
    private function createHtml()
    {
        $html = new \Nette\Utils\Html();
        $html->addToHtmlArray('someContent')['hey'];
    }
}

?>
