<?php

if ($foo == 'my string') {
    $result = 'ok';
} else {
    $result = 'not ok';
}

if ($foo == 'my string') {
    $result = 'ok';
}

if ($foo == 'my string') {
    $result = 'ok';
} elseif ($foo == 'your string') {
    $result = 'not ok';
}

switch ($foo) {
    case 'my string':
        $result = 'ok';
        break;
    case 'your string':
        $result = 'not ok';
        break;
    default:
        $result = 'too much';
}
