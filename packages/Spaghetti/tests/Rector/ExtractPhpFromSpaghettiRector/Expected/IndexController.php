<?php

class IndexController
{
    public function render()
    {
        return ['variable1' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']];
    }
}