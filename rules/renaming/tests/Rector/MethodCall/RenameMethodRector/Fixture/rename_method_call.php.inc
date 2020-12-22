<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class RenameMethodCall
{
    private function createHtml()
    {
        $html = new \Nette\Utils\Html();
        $html->add('someContent');

        $anotherHtml = $html;
        $anotherHtml->add('someContent');
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class RenameMethodCall
{
    private function createHtml()
    {
        $html = new \Nette\Utils\Html();
        $html->addHtml('someContent');

        $anotherHtml = $html;
        $anotherHtml->addHtml('someContent');
    }
}

?>
