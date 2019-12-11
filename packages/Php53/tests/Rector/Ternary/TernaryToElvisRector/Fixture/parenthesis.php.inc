<?php

function elvisRemoveParenthesis()
{
    $value = ($a - $b) ? ($a - $b) : $c;
}

?>
-----
<?php

function elvisRemoveParenthesis()
{
    $value = ($a - $b) ?: $c;
}

?>
