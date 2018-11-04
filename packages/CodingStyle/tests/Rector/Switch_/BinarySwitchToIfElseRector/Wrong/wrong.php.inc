<?php

switch ($foo) {
    case 'my string':
        $result = 'ok';
    break;

    default:
        $result = 'not ok';
}

switch ($foo) {
    case 'my string':
        $result = 'ok';
        break;
}

switch ($foo) {
    case 'my string':
        $result = 'ok';
        break;
    case 'your string':
        $result = 'not ok';
        break;
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
