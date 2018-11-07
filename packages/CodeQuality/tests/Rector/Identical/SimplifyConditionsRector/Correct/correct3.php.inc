<?php declare(strict_types=1);

if ($a === '-' && ! $start && ! ($i < $l && $s[$i] === ']')) {
    echo 'maybe';
}

if (!($orderItem instanceof OrderTransport || $orderItem instanceof OrderPayment)) {
    $itemsWithoutTransportAndPayment[] = $orderItem;
}
