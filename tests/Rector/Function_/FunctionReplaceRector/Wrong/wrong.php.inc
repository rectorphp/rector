<?php

class SomeClass
{
    public function someMethod()
    {
        view('template', []);
    }
}

function a(callable $a) {
    $a();
}
