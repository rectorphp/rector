<?php

function simplifyConditions3()
{
    if ($a === '-' && !$start && !($i < $l && $s[$i] === ']')) {
        echo 'maybe';
    }

    if (!($orderItem instanceof OrderTransport || $orderItem instanceof OrderPayment)) {
        $itemsWithoutTransportAndPayment[] = $orderItem;
    }
}

?>
-----
<?php

function simplifyConditions3()
{
    if ($a === '-' && !$start && !($i < $l && $s[$i] === ']')) {
        echo 'maybe';
    }

    if (!($orderItem instanceof OrderTransport || $orderItem instanceof OrderPayment)) {
        $itemsWithoutTransportAndPayment[] = $orderItem;
    }
}

?>
