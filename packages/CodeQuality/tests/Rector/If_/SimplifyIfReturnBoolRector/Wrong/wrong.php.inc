<?php

use PhpCsFixer\Tokenizer\Token;

function simplifyIfReturnBool()
{
    $docToken = new Token([]);
    if (strpos($docToken->getContent(), "\n") === false) {
        return true;
    }

    return false;


    if (strpos($docToken->getContent(), "\n") !== false) {
        return true;
    }

    return false;


    if (strpos($docToken->getContent(), "\n") !== false) {
        return true;
    }

    return 5;
}

?>
-----
<?php

use PhpCsFixer\Tokenizer\Token;

function simplifyIfReturnBool()
{
    $docToken = new Token([]);
    return strpos($docToken->getContent(), "\n") === false;
    return strpos($docToken->getContent(), "\n") !== false;


    if (strpos($docToken->getContent(), "\n") !== false) {
        return true;
    }

    return 5;
}

?>
