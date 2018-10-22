<?php

function someFunction()
{
    if ($this) {
    } else {
        // nested!
        $items = [1, 2, 3];
        $lastKey = array_key_last($items);
    }
}
