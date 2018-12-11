<?php

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

function methodNameReplacer()
{
    $html = new \Nette\Utils\Html();
    $html->addHtml('someContent');
    $html->addHtmlArray('someContent')['hi'];
    $html->{$value}();
}

?>
