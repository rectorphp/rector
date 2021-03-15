<?php

function simplifyIfNotNullReturnRector2()
{
    $newNode = 'something';
    if ($newNode === null) {
        return null;
    }

    return $newNode;


    if (null === $newNode) {
        return null;
    }

    return $newNode;

    if (93 === $newNode) {
        return 93;
    }

    return $newNode;

    if (93 === $newNode) {
        return 94;
    }

    return $newNode;
}

?>
-----
<?php

function simplifyIfNotNullReturnRector2()
{
    $newNode = 'something';

    return $newNode;

    return $newNode;

    return $newNode;

    if (93 === $newNode) {
        return 94;
    }

    return $newNode;
}

?>
