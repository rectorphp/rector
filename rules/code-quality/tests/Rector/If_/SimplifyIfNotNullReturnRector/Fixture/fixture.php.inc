<?php

function simplifyIfNotNullReturnRector()
{
    $newNode = 'something';
    if ($newNode !== null) {
        return $newNode;
    }

    return null;

    if (null !== $newNode) {
        return $newNode;
    }

    return null;
}

?>
-----
<?php

function simplifyIfNotNullReturnRector()
{
    $newNode = 'something';
    return $newNode;
    return $newNode;
}

?>
