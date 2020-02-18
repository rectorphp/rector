<?php

function foo($foo, $baz)
{
    return $foo == 'bar' ? 'it is bar' : (isset($baz) ? 'baz' : 'bizz');
}