<?php

function methodNameReplacer()
{
    $html = new \Nette\Utils\Html();
    $html->add('someContent');
    $html->{$value}();
}

?>
-----
<?php

function methodNameReplacer()
{
    $html = new \Nette\Utils\Html();
    $html->addHtml('someContent');
    $html->{$value}();
}

?>
