<?php

function foo($foo, $bar)
{
    return $foo == 'foo' ? 'it is foo' : (isset($bar) ? 'bar' : 'baz');
}