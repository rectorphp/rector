<?php

function isCountable()
{
    is_array($foo) || $foo instanceof Countable;
}

?>
