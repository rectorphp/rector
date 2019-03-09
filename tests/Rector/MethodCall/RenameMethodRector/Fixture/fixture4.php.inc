<?php

namespace Rector\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

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

namespace Rector\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

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
