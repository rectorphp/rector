<?php

function paramAndStaticVarName()
{
    $paramNode = new PhpParser\Node\Param;
    $paramName = $paramNode->name;
}

?>
-----
<?php

function paramAndStaticVarName()
{
    $paramNode = new PhpParser\Node\Param;
    $paramName = $paramNode->var->name;
}

?>
