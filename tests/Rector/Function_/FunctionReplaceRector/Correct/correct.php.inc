<?php

class SomeClass
{
    public function someMethod()
    {
        \Laravel\Templating\render('template', []);
    }
}

function a(callable $a) {
    $a();
}
