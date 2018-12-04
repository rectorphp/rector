<?php

function identifier()
{
    $constNode = new \PhpParser\Node\Const_;
    $name = $constNode->name;
}

?>
-----
<?php

function identifier()
{
    $constNode = new \PhpParser\Node\Const_;
    $name = $constNode->name->toString();
}

?>
