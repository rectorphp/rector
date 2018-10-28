<?php

$newNode = 'something';
if ($newNode !== null) {
    return $newNode;
}

return null;

if (null !== $newNode) {
    return $newNode;
}

return null;
