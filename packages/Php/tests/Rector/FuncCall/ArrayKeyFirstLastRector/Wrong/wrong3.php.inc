<?php

function someFunction()
{
    if ($this) {
    } else {
        // nested!
        $items = [1, 2, 3];
        end($items);
        $lastKey = key($items);
    }
}
