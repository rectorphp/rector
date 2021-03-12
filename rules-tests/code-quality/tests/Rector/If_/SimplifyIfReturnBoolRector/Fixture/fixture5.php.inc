<?php

function simplifyIfReturnBool5()
{
    if (!strpos($docToken->getContent(), "\n")) {
        return false;
    }

    return true;
}

?>
-----
<?php

function simplifyIfReturnBool5()
{
    return (bool) strpos($docToken->getContent(), "\n");
}

?>
