<?php

function simplifyIfReturnBool2()
{
    if (strpos($docToken->getContent(), "\n")) {
        return false;
    }

    return true;

    if (!strpos($docToken->getContent(), "\n")) {
        return true;
    }

    return false;

    if (!strpos($docToken->getContent(), "\n")) {
        return true;
    }

    return 5;
}

?>
-----
<?php

function simplifyIfReturnBool2()
{
    return !strpos($docToken->getContent(), "\n");
    return !strpos($docToken->getContent(), "\n");
    if (!strpos($docToken->getContent(), "\n")) {
        return true;
    }

    return 5;
}

?>
