<?php

function catchAndClosureUseName()
{
    $closureUseNode = new PhpParser\Node\Expr\ClosureUse;
    $string = $closureUseNode->var;

    $string = $closureUseNode->var->name;
}

?>
-----
<?php

function catchAndClosureUseName()
{
    $closureUseNode = new PhpParser\Node\Expr\ClosureUse;
    $string = $closureUseNode->var->name;

    $string = $closureUseNode->var->name;
}

?>
