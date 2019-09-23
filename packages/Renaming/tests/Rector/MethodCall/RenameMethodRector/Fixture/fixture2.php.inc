<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

function methodNameReplacer()
{
    $html = new \Nette\Utils\Html();
    $html->add('someContent');
    $html->addToArray('someContent');
    $html->{$value}();
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

function methodNameReplacer()
{
    $html = new \Nette\Utils\Html();
    $html->addHtml('someContent');
    $html->addHtmlArray('someContent')['hi'];
    $html->{$value}();
}

?>
