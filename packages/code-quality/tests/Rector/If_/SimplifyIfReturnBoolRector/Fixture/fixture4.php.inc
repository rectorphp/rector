<?php

function simplifyIfReturnBool4()
{
    if (!$this->isClassyType($phpDocNode->name)) {
        return true;
    }

    return false;
}

?>
-----
<?php

function simplifyIfReturnBool4()
{
    return !$this->isClassyType($phpDocNode->name);
}

?>
