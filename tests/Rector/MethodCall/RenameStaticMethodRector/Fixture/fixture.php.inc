<?php

function staticMethodNameReplacer()
{
    Nette\Utils\Html::add('someContent');
}

?>
-----
<?php

function staticMethodNameReplacer()
{
    Nette\Utils\Html::addHtml('someContent');
}

?>
