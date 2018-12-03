<?php

function simplifyIfReturnBool6()
{
    if (! $parentNode) {
        return true;
    }

    return false;

    if ($parentNode) {
        return true;
    }

    return false;
}

?>
-----
<?php

function simplifyIfReturnBool6()
{
    return ! $parentNode;
    return (bool) $parentNode;
}

?>
